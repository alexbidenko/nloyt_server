<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Events\OrderServiceEvent;
use App\Events\OrderWasReceived;
use App\Events\ServiceBusyEvent;
use App\Orders\Order;
use App\Orders\OrderConclusion;
use App\Orders\OrderConnectionLog;
use App\Orders\OrderFile;
use App\Orders\OrderHistory;
use App\Orders\OrderReceipt;
use App\Service;
use App\ServiceDevice;
use App\User;
use App\UserDevice;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{

    function getUserOrdersPaginate($page, $perPage, Request $request) {
        $user = User::getUser($request->header('Authorization'));

        $devicePins = DB::table('user_devices')->where('ownerId', $user->id)->pluck('pin');
        if($perPage == 0) {
            $orders = DB::table('orders')->whereIn('devicePin', $devicePins)
                ->orderByDesc('updated_at')->get();
        } else {
            $orders = DB::table('orders')->whereIn('devicePin', $devicePins)
                ->orderByDesc('updated_at')->forPage($page, $perPage)->get();
        }
        for($i = 0; $i < count($orders); $i++) {
            $orders[$i]->serviceName = Service::whereId($orders[$i]->serviceId)->value('serviceName');
            $statusText = '';
            switch ($orders[$i]->status) {
                case 1:
                    $statusText = 'Request sent';
                    break;
                case 2:
                    $statusText = 'In work';
                    break;
                case 3:
                    $statusText = 'Canceled by client';
                    break;
                case 4:
                    $statusText = 'Rejected by workshop';
                    break;
                case 5:
                    $statusText = 'Interrupted by client';
                    break;
                case 6:
                    $statusText = 'Interrupted by workshop';
                    break;
                case 7:
                    $statusText = 'Receipt approval';
                    break;
                case 8:
                    $statusText = 'Done';
                    break;
                case 9:
                    $statusText = 'Scheduled';
                    break;
                case 10:
                    $statusText = 'Overdue';
                    break;
                case 11:
                    $statusText = 'Live session';
                    break;
                case 12:
                    $statusText = 'Stop session';
                    break;
            }
            $orders[$i]->status = [
                'code' => $orders[$i]->status,
                'text' => $statusText
            ];
        }
        return $this->createResponse($orders);
    }

    function getUserOrders(Request $request) {
        return $this->getUserOrdersPaginate(0, 0, $request);
    }

    function getServiceOrdersPaginate($page, $perPage, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        if($perPage == 0) {
            $orders = Order::query()->where('serviceId', $service->id)
                ->orderByDesc('updated_at')->with('files')->get();
        } else {
            $orders = Order::query()->where('serviceId', $service->id)
                ->orderByDesc('updated_at')->forPage($page, $perPage)->with('files')->get();
        }


        $devicesPin = $orders->map(function ($order) { return $order->devicePin; });

        $userDevices = UserDevice::query()->whereIn('pin', $devicesPin)->get();
        $userDevicesData = [];
        foreach ($orders as $order) {
            $findDevice = null;
            foreach ($userDevices as $device) {
                if($order->devicePin == $device->pin) {
                    $findDevice = $device->toArray();
                    if(Schema::hasTable('device_logs_'.$findDevice['pin'])) {
                        $findDevice['data'] = DB::table('device_logs_' . $findDevice['pin'])->latest()->first();
                    }
                }
            }
            if($findDevice != null) {
                $order->append('services');
                $findDevice['order'] = $order->toArray();
                $userDevicesData[] = $findDevice;
            }
        }

        return $this->createResponse($userDevicesData);
    }

    function getServiceOrders(Request $request) {
        return $this->getServiceOrdersPaginate(0, 0, $request);
    }

    function userUpdateStatus($orderId, Request $request) {
        User::getUser($request->header('Authorization'));

        $order = Order::whereId($orderId)->first();

        if($order->status == 1 && $request->all()['newStatus'] == 3) {
            $order->status = 3;
            $order->timeStart = 0;
        } elseif(($order->status == 2 || $order->status == 11) && $request->all()['newStatus'] == 5) {
            $order->status = $request->all()['newStatus'];
            $order->duration = time() - $order->timeStart;

            $serviceDevice = ServiceDevice::whereServiceId($order->serviceId)->where('connected_to', $order->devicePin)->first();

            file_get_contents('https://nloyt.azurewebsites.net/stop/' . $serviceDevice->active_bridge);

            $serviceDevice->is_busy = false;
            $serviceDevice->connected_to = null;
            $serviceDevice->active_bridge = null;
            $serviceDevice->save();
        } elseif($order->status == 7 && $request->all()['newStatus'] == 8) {
            $order->status = 8;

            $orderReceipt = OrderReceipt::whereOrderId($order->id)->first();
            $orderReceipt->is_approved = true;
            $orderReceipt->save();
        } elseif($order->status == 9 && $request->all()['newStatus'] == 9) {
            $order->status = 9;
            $order->timeStart = $request->all()['timeStart'];
            $order->isStarted = false;
        } else {
            return $this->createResponse((object) [], false, [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Not correct status'
            ]);
        }

        $order->updated_at = date_create()->format('Y-m-d H:i:s');

        $order->save();
        OrderHistory::query()->insert([
            'orderId' => $order->id,
            'status' => $order->status,
            'timestamp' => time()
        ]);

        $order->append('services');
        event(new OrderServiceEvent(
            $order->toArray(),
            UserDevice::wherePin($order->devicePin)->first()->toArray()
        ));

        $activeOrdersCount = Order::query()
            ->where(['serviceId' => $order->serviceId])
            ->whereIn('status', [11])->count();
        event(new ServiceBusyEvent($order->serviceId, $activeOrdersCount > 0, $activeOrdersCount));

        return $this->createResponse($order);
    }

    function serviceUpdateStatus($orderId, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        $order = Order::query()->where('id', $orderId)->where('serviceId', $service->id)->firstOr(function () {
            Controller::throwError(Response::HTTP_FORBIDDEN, 'Not your order');
        });

        if($request->all()['newStatus'] == 2) {
            $order->status = 2;
            $order->timeStart = 0;
            $order->isStarted = false;
        } elseif ($request->all()['newStatus'] == 11) {
            $serviceDevice = ServiceDevice::whereServiceId($service->id)->where('is_busy', false)->firstOr(function () {
                Controller::throwError(Response::HTTP_CONFLICT, 'Free device not exists');
            });

            $bridge = trim(file_get_contents('https://nloyt.azurewebsites.net/start'));

            Controller::sendRabbitMQMessage('pin_' . $order->devicePin, json_encode([
                'action' => 'bridge',
                'url' => 'https://nloyt.azurewebsites.net/' . $bridge
            ]));
            Controller::sendRabbitMQMessage('pin_' . $serviceDevice->pin, json_encode([
                'action' => 'bridge',
                'url' => 'https://nloyt.azurewebsites.net/' . $bridge
            ]));

            $serviceDevice->is_busy = true;
            $serviceDevice->connected_to = $order->devicePin;
            $serviceDevice->active_bridge = $bridge;
            $serviceDevice->save();

            $log = new OrderConnectionLog;
            $log->order_id = $order->id;
            $log->user_device_pin = $order->devicePin;
            $log->service_device_pin = $serviceDevice->pin;
            $log->timestamp_start = time();
            $log->bridge = $bridge;
            $log->save();

            $order->status = 11;
            $order->timeStart = $log->timestamp_start;
            $order->isStarted = true;
        } elseif ($request->all()['newStatus'] == 12) {
            $serviceDevice = ServiceDevice::whereServiceId($service->id)->where('connected_to', $order->devicePin)->first();

            $logs = file_get_contents('https://nloyt.azurewebsites.net/stop/' . $serviceDevice->active_bridge);

            $serviceDevice->is_busy = false;
            $serviceDevice->connected_to = null;
            $serviceDevice->active_bridge = null;
            $serviceDevice->save();

            $log = OrderConnectionLog::whereOrderId($order->id)->where('timestamp_end', null)->first();
            $log->message = $logs;
            $log->timestamp_end = time();
            $log->save();

            $order->status = 12;
            $order->duration = time() - $order->timeStart;
            $order->isStarted = false;
        } elseif ($request->all()['newStatus'] == 4) {
            $order->status = 4;
            $order->timeStart = 0;
            $order->isStarted = false;
        } elseif ($request->all()['newStatus'] == 6) {
            $order->status = 6;
            $order->duration = time() - $order->timeStart;
            $order->isStarted = false;

            $serviceDevice = ServiceDevice::whereServiceId($service->id)->where('connected_to', $order->devicePin)->first();

            file_get_contents('https://nloyt.azurewebsites.net/stop/' . $serviceDevice->active_bridge);

            $serviceDevice->is_busy = false;
            $serviceDevice->connected_to = null;
            $serviceDevice->active_bridge = null;
            $serviceDevice->save();
        } elseif ($request->all()['newStatus'] == 7) {
            $order->status = 7;
            $order->isStarted = false;

            $orderReceipt = new OrderReceipt;
            $orderReceipt->order_id = $order->id;
            $orderReceipt->employee_id = $admin->id;
            $orderReceipt->create_time = time();
            $orderReceipt->save();
        } elseif ($request->all()['newStatus'] == 9) {
            $order->status = 9;
            $order->timeStart = 0;
            $order->isStarted = false;
        } elseif ($request->all()['newStatus'] == 10) {
            $order->status = 10;
            $order->duration = 0;
            $order->isStarted = false;
        } else {
            return $this->createResponse((object) [], false, [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Not correct command'
            ]);
        }

        $order->updated_at = date_create()->format('Y-m-d H:i:s');

        $order->save();
        OrderHistory::query()->insert([
            'orderId' => $order->id,
            'status' => $order->status,
            'timestamp' => time()
        ]);

        $device = UserDevice::wherePin($order->devicePin)->first();
        event(new OrderWasReceived($order->toArray(), $device->toArray()));
        $order->append('services');
        event(new OrderServiceEvent($order->toArray(), $device->toArray()));

        $activeOrdersCount = Order::query()
            ->where(['serviceId' => $order->serviceId])
            ->whereIn('status', [11])->count();
        event(new ServiceBusyEvent($order->serviceId, $activeOrdersCount > 0, $activeOrdersCount));

        return $this->createResponse($order);
    }

    function getTypes(Request $request) {
        return $this->createResponse(json_decode(file_get_contents(resource_path().'/json/orderTypes.json'), true));
    }

    function getStatuses(Request $request) {
        return $this->createResponse(json_decode(file_get_contents(resource_path().'/json/orderStatuses.json'), true));
    }

    function getOrderById($orderId, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        $order = Order::whereId($orderId)->where('serviceId', $service->id)->with('files')->with('conclusions')->firstOr(function () {
            Controller::throwError(Response::HTTP_BAD_REQUEST, 'Device not found');
        });
        $order->append('services');

        $device = DB::table('user_devices')->where('pin', $order->devicePin)->first();
        $device->order = $order;
        $device->data = DB::table('device_logs_'.$device->pin)->latest()->first();
        $device->owner = User::whereId($device->ownerId)->first();
        $device->owner->ordersCount = Order::query()->whereIn('devicePin',
            UserDevice::query()->where('ownerId', $device->owner->id)->pluck('pin'))->count();

        return $this->createResponse($device);
    }

    function getOrderErrors($orderId, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        $pin = Order::query()->where(['serviceId' => $service->id, 'id' => $orderId])->value('devicePin');

        if(!Schema::hasTable('device_logs_'.$pin))
            return $this->createResponse([
                'errors' => [],
                'isFinish' => true
            ], true, ['code' => 404, 'Table not found']);

        $deviceErrorConfig = json_decode(file_get_contents(resource_path().'/json/service_config.json'));
        $permissibleValues = ['TP', 'CL', 'OL', 'FL', 'BV'];

        $queries = [];
        $result = [];
        $timeBorder = (int) $_GET['olderThan'];
        for($i = 0; $i < 10; $i++) {
            $queries[$i] = DB::table('device_logs_'.$pin);
            $queries[$i]->where(function (Builder $query) use ($deviceErrorConfig, $permissibleValues) {
                foreach ($deviceErrorConfig as $config) {
                    if(in_array($config->type, $permissibleValues)) {
                        $query->orWhereNotBetween($config->type, [$config->min, $config->max]);
                    }
                }
            });

            if($queries[$i]->where('created_at', '<', date('Y-m-d H:i:s', $timeBorder))->exists()) {
                $newData = (array) $queries[$i]->where('created_at', '<', date('Y-m-d H:i:s', $timeBorder))
                    ->latest()->first();
                array_push($result, $newData);
                $timeBorder = strtotime($newData['created_at']) - 60 * 60 * 6;
            } else {
                return $this->responseSuccess([
                    'errors' => $result,
                    'isFinish' => true
                ]);
            }
        }

        return $this->responseSuccess([
            'errors' => $result,
            'isFinish' => false
        ]);
    }

    function addFilesToOrder($orderId, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        $prepareResponse = [];
        for ($i = 0; $i < (int) $request->all()['filesCount']; $i++) {
            $file = $request->file('file'.$i);

            $filename = $file->getClientOriginalName();
            if(strlen($file->getClientOriginalName()) > 200) {
                $filename = substr($file->getClientOriginalName(), 0, 190) . $file->getMimeType();
            }
            $file->storeAs('s'.$service->id.'/order_files', $filename, 'private_service');

            $fileData = [
                'serviceId' => $service->id,
                'orderId' => $orderId,
                'timestamp' => time(),
                'filename' => $filename
            ];
            $fileData['id'] = DB::table('order_files')->insertGetId($fileData);
            array_push($prepareResponse, $fileData);
        }

        return $this->createResponse($prepareResponse);
    }

    function getOrderFile($filename, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        if(!Storage::disk('private_service')->exists('s'.$service->id.'/order_files/'.$filename)) {
            return response()->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('private_service')->download('s'.$service->id.'/order_files/'.$filename);
    }

    function deleteOrderFile($filename, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        if(Storage::disk('private_service')->exists('s'.$service->id.'/order_files/'.$filename)) {
            Storage::disk('private_service')->delete('s'.$service->id.'/order_files/'.$filename);
        }

        OrderFile::whereFilename($filename)->forceDelete();

        return $this->createResponse(['result' => 'success']);
    }

    function addConclusionToOrder($orderId, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        $conclusionData = [
            'serviceId' => $service->id,
            'orderId' => $orderId,
            'timestamp' => time(),
            'text' => $request->all()['text'],
            'risk' => $request->all()['risk']
        ];
        $conclusionData['id'] = DB::table('order_conclusions')->insertGetId($conclusionData);

        return $this->responseSuccess($conclusionData);
    }

    function updateConclusions($conclusionId, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        OrderConclusion::query()->where(['id' => $conclusionId, 'serviceId' => $service->id])
            ->update([
                'text' => $request->all()['text'],
                'risk' => $request->all()['risk']
            ]);

        return $this->responseSuccess(
            OrderConclusion::query()->where(['id' => $conclusionId, 'serviceId' => $service->id])->first()
        );
    }

    function deleteConclusions($conclusionId, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        OrderConclusion::query()->where(['id' => $conclusionId, 'serviceId' => $service->id])->forceDelete();

        return $this->responseSuccess(['result' => 'success']);
    }

    function getReceipt($orderId, Request $request) {
        if(User::whereToken($request->header('Authorization'))->exists())
            $userType = 'user';
        else if(Employee::whereToken($request->header('Authorization'))->exists())
            $userType = 'employee';
        else return $this->responseError(Response::HTTP_NOT_FOUND, 'User not found');

        $receipt = OrderReceipt::whereOrderId($orderId)->first();
        if(!isset($receipt))
            return $this->responseError(Response::HTTP_BAD_REQUEST, 'Receipt not found or not created');

        $order = Order::getFullOrder($orderId);
        $service = Service::whereId($order->serviceId)->first();
        $device = UserDevice::wherePin($order->devicePin)->first();
        $user = User::whereId($device->ownerId)->first();
        $history = OrderHistory::query()->where('orderId', $order->id)->get()->toArray();
        $employee = Employee::whereId($receipt->employee_id)->first();

        $language = $request->header('Language');
        switch ($language) {
            case 'ru':
            case 'en':
                break;
            default:
                $language = 'en';
        }

        $user->phone = $this->phoneToMaskAuto($user->phone);
        $token = $request->header('Authorization');

        $logs = OrderConnectionLog::whereOrderId($order->id)->get();

        return view('order-receipt')
            ->with(compact('order'))
            ->with(compact('service'))
            ->with(compact('history'))
            ->with(compact('device'))
            ->with(compact('employee'))
            ->with(compact('user'))
            ->with(compact('language'))
            ->with(compact('token'))
            ->with(compact('userType'))
            ->with(compact('logs'));
    }
}
