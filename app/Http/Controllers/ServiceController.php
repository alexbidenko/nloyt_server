<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Orders\Order;
use App\Service;
use App\ServiceDevice;
use App\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ServiceController extends Controller
{

    function getServices() {
        $data = DB::table('services')->get();

        for($i = 0; $i < count($data); $i++) {
            $data[$i]->autoMarks = json_decode($data[$i]->autoMarks);
            $data[$i]->equipmentsAndSoftware = json_decode($data[$i]->equipmentsAndSoftware);
            $data[$i]->servicePhotos = json_decode($data[$i]->servicePhotos);
        }

        return $this->createResponse($data);
    }

    function addDevice($pin, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId);

        if(ServiceDevice::wherePin($pin)->exists() || UserDevice::wherePin($pin)->exists())
            return $this->createResponse((object) [], false, [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Device is added'
            ]);

        $device = new ServiceDevice;
        $device->service_id = $service->id;
        $device->pin = $pin;
        $device->save();

        return $this->createResponse(['result' => 'success']);
    }

    function addEmployees($serviceId, Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $newData = $request->all();

        $service = DB::table('services')->where('id', $admin->serviceId)->first();

        for($i = 0; $i < (int) $newData['employeesCount']; $i++) {
            $path = time().'_'.Str::random(8).'.'.$request->file('photo'.$i)->getClientOriginalExtension();
            $request->file('photo'.$i)->storeAs('', $path, 'public_uploads_employee');

            $passwordKey = Str::random(20);
            $newEmployee = [
                'name' => $newData['name'.$i],
                'serviceId' => $service->id,
                'phone' => $this->fixPhone($newData['phone'.$i]),
                'email' => $newData['email'.$i],
                'serviceName' => $newData['serviceName'.$i],
                'position' => $newData['position'.$i],
                'roles' => $newData['roles'.$i],
                'photo' => $path,
                'password' => 'empty:'.$passwordKey,
                'token' => Str::random(20),
                'created_at' => date_create()->format('Y-m-d H:i:s'),
                'updated_at' => date_create()->format('Y-m-d H:i:s')
            ];

            $message = 'Здравствуйте! Вы были зарегистрированы на платформе NLOYT как сотрудник сервисного центра '.$newData['serviceName'.$i].'\n'.
                'Ниже отправляем вам ссылку на форму установления пароля и переход в Ваш личный кабинет\n\n'.
                'http://194.182.85.89/employee/password?key='.$passwordKey;

            // mail($newData['email'.$i], 'Ссылка на форму установления пароля NLOUT', $message);
            $url = 'https://admire.social/sendMail.php';
            $data = array(
                'address' => $newData['email'.$i],
                'subject' => 'Ссылка на форму установления пароля NLOUT',
                'message' => $message,
                'from' => 'support@nloyt.com'
            );

            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result === FALSE) { /* Handle error */ }

            // echo var_dump($result);

            DB::table('employees')->insert($newEmployee);
        }

        return $this->createResponse(['result' => 'success']);
    }

    function employeeChangePassword(Request $request) {
        if(DB::table('employees')->where('password', 'empty:'.$request->header('Password-Key'))->exists()) {
            $employee = DB::table('employees')->
            where('password', 'empty:'.$request->header('Password-Key'))
                ->first();

            DB::table('employees')->
                where('password', 'empty:'.$request->header('Password-Key'))->
                update(['password' => Hash::make($request->all()['password'])]);

            return $this->createResponse([
                'token' => $employee->token,
                'service' => DB::table('services')->where('id', $employee->serviceId)->first()
            ]);
        } else {
            return $this->createResponse((object) [], false, [
                'code' => 404,
                'message' => 'User not found'
            ]);
        }
    }

    function addPurchase(Request $request) {
        if(Employee::whereToken($request->header('Authorization'))->exists()) {
            $admin = Employee::whereToken($request->header('Authorization'))->first();

            $message = 'Данные для покупки:\n'.json_encode($request->all());
            $url = 'https://admire.social/sendMail.php';
            $data = array(
                'address' => 'rbadgiev@gmail.com',
                'subject' => 'Заявка на покупку',
                'message' => $message,
                'from' => 'support@nloyt.com'
            );

            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result === FALSE) { /* Handle error */ }

            return $this->createResponse(['result' => 'success']);
        } else {
            return $this->createResponse((object) [], false, [
                'code' => 404,
                'message' => 'User not found'
            ]);
        }
    }


    function getDevicesStatusPaginate($page, $perPage, Request $request)
    {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        if($perPage == 0) {
            $pinsList = Order::query()->where('serviceId', $service->id)->groupBy('devicePin')->pluck('devicePin');
        } else {
            $pinsList = Order::query()->where('serviceId', $service->id)->groupBy('devicePin')->forPage($page, $perPage)->pluck('devicePin');
        }


        $devicesList = UserDevice::query()->whereIn('pin', $pinsList)->get();
        for($i = 0; $i < count($devicesList); $i++) {
            if(Schema::hasTable('device_logs_' . $devicesList[$i]->pin)) {
                $devicesList[$i]->data = DB::table('device_logs_' . $devicesList[$i]->pin)->latest()->first();
                $devicesList[$i]->onlineService = DB::table('orders')
                    ->where('devicePin', $devicesList[$i]->pin)
                    ->whereIn('status', [1, 2, 7, 9])->exists();
            }
        }

        return $this->createResponse($devicesList);
    }

    function getDevicesStatus(Request $request)
    {
        return $this->getDevicesStatusPaginate(0, 0, $request);
    }

    function getAllDataCounts(Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        $result = [];
        $statuses = json_decode(file_get_contents(resource_path('json/orderStatuses.json')));
        foreach ($statuses as $status) {
            array_push($result, [
                'statusId' => $status->id,
                'count' => DB::table('orders')->where(['status' => $status->id, 'serviceId' => $service->id])->count()
            ]);
        }

        return $this->createResponse([
            'orders' => $result
        ]);
    }

    function getServiceConfig(Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));
        $service = Service::whereId($admin->serviceId)->first();

        $activeOrdersCount = DB::table('orders')
            ->where(['serviceId' => $service->id])
            ->whereIn('status', [11])->count();

        $ordersCount = [];
        for($i = 1; $i <= 12; $i++) {
            $ordersCount['status_'.$i] =
                DB::table('orders')->where(['status' => $i, 'serviceId' => $service->id])->count();
        }

        return $this->createResponse([
            'isBusy' => $activeOrdersCount > 0,
            'activeOrdersCount' => $activeOrdersCount,
            'config' => json_decode(file_get_contents(resource_path().'/json/service_config.json'), true),
            'ordersCount' => $ordersCount
        ]);
    }
}
