<?php

namespace App\Http\Controllers;

use App\Events\OrderServiceEvent;
use App\Orders\Order;
use App\Orders\OrderFile;
use App\ServicesList;
use App\User;
use App\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserOrderController extends Controller
{

    function addOrder($pin, Request $request) {
        $validatorResult = Validator::make($request->all(), [
            'serviceId' => 'required|numeric',
            'services_ids' => 'required|string',
            'timeStart' => 'numeric|min:'.(time() - 10),
        ]);
        if($validatorResult->fails())
            return $this->createResponse((object) [], false, [
                'code' => 0,
                'message' => json_encode($validatorResult->failed())
            ]);

        $user = User::getUser($request->header('Authorization'));

        if(DB::table('user_devices')->where(['pin' => $pin])->where('ownerId', '<>', $user->id)->exists())
            return $this->createResponse((object) [], false, [
                'code' => 403,
                'message' => 'It\'s not your device'
            ]);

        if(DB::table('orders')->where(['devicePin' => $pin, 'status' => 1])->where('timeStart', '>', time() - 60 * 30)->exists())
            return $this->createResponse((object) [], false, [
                'code' => 403,
                'message' => 'You have active order'
            ]);

        $servicesIds = array_map(function($el) {
            return (int) $el;
        }, array_filter(explode(',', $validatorResult->validated()['services_ids']), function($el) {
            return is_numeric($el);
        }));
        if(count($servicesIds) > 0) {
            $servicesIds = ServicesList::whereServiceId($validatorResult->validated()['serviceId'])->whereIn('id', $servicesIds)->pluck('id');
        } else {
            $servicesIds = ServicesList::whereServiceId($validatorResult->validated()['serviceId'])->where('services_id', 1)->pluck('id');
        }

        if(count($servicesIds) == 0)
            return $this->responseError(Response::HTTP_BAD_REQUEST, 'Not know services list');

        $timeStart = time();
        if(isset($request->all()['timeStart']) && $request->all()['timeStart'] > 0)
            $timeStart = $request->all()['timeStart'];

        $newOrder = [
            'devicePin' => $pin,
            'serviceId' => $request->all()['serviceId'],
            'status' => 1,
            'services_ids' => json_encode($servicesIds),
            'timeStart' => $timeStart,
            'duration' => 0,
            'isStarted' => false,
            'created_at' => date_create()->format('Y-m-d H:i:s'),
            'updated_at' => date_create()->format('Y-m-d H:i:s'),
            'requestCode' => 'RU-002-'
                . str_pad($request->all()['serviceId'],  4, "0", STR_PAD_LEFT)
                . '-'. substr((string) getdate()['year'], -2) .'-' . getdate()['mon'] .'-'
                . (Order::where('serviceId', $request->all()['serviceId'])->count() + 1)
        ];

        $newOrder['id'] = DB::table('orders')->insertGetId($newOrder);

        DB::table('order_history')->insert([
            'orderId' => $newOrder['id'],
            'status' => 1,
            'timestamp' => time()
        ]);

        $device = DB::table('user_devices')->where('pin', $pin)->first();
        if(Schema::hasTable('device_logs_'.$device->pin)) {
            $device->data = DB::table('device_logs_' . $device->pin)->latest()->first();
        }

        $order = Order::whereId($newOrder['id'])->first();
        $order->append('services');
        event(new OrderServiceEvent($order->toArray(), $device));

        return $this->createResponse($newOrder);
    }

    function getOrderFile($orderId, $filename, Request $request) {
        $order = Order::whereId($orderId)->first();

        $user = User::getUser($request->header('Authorization'));

        if(!UserDevice::wherePin($order->devicePin)->where('ownerId', $user->id)->exists())
            return $this->responseError(Response::HTTP_FORBIDDEN, 'Order not found');

        if(!OrderFile::whereFilename($filename)->where('orderId', $order->id)->exists())
            return $this->responseError(Response::HTTP_CONFLICT, 'File not found');

        if(!Storage::disk('private_service')->exists('s'.$order->serviceId.'/order_files/'.$filename)) {
            return response()->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return Storage::disk('private_service')->download('s'.$order->serviceId.'/order_files/'.$filename);
    }
}
