<?php

namespace App\Http\Controllers;

use App\Orders\Order;
use App\Service;
use App\ServicesList;
use App\ServicesTemplate;
use App\User;
use App\UserDevice;
use App\VerifyCode;
use DateTime;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{

    function addOrCheckUser(Request $request)
    {
        $validatorResult = Validator::make($request->all(), [
            'phone' => 'required'
        ]);
        if($validatorResult->fails()) {
            return $this->createResponse((object) [], false, [
                'code' => 0,
                'message' => json_encode($validatorResult->failed())
            ]);
        }

        $phone = $this->fixPhone($request->all()['phone']);
        if (DB::table('users')->where('phone', $phone)->exists()) {
            $user = DB::table('users')->where('phone', $phone)->first();
            unset($user->password);
            unset($user->token);
            $this->sendSMS($phone, $user->id);

            return $this->createResponse($user);
        } else {
            $token = Str::random(20);
            $newUser = [
                'phone' => $phone,
                'token' => $token,
                'isConfirm' => false,
                'created_at' => date_create()->format('Y-m-d H:i:s'),
                'updated_at' => date_create()->format('Y-m-d H:i:s')
            ];
            $id = DB::table('users')->insertGetId($newUser);
            $newUser['id'] = $id;
            $this->sendSMS($phone, $id);
            unset($newUser['token']);
            unset($newUser['isConfirm']);
            return $this->createResponse($newUser);
        }
    }

    function getUser(Request $request)
    {
        $user = User::getUser($request->header('Authorization'))->toArray();

        unset($user['token']);

        return $this->createResponse($user);
    }

    function updateUser(Request $request)
    {
        $validatorResult = Validator::make($request->all(), [
            'phone' => 'min:8|numeric',
            'email' => 'email',
            'firstName' => 'string',
            'lastName' => 'string',
            'paymentMethod' => 'string',
        ]);
        if($validatorResult->fails())
            return $this->createResponse((object) [], false, [
                'code' => 0,
                'message' => json_encode($validatorResult->failed())
            ]);

        $data = $validatorResult->validated();

        $user = User::getUser($request->header('Authorization'));

        $data['updated_at'] = date_create()->format('Y-m-d H:i:s');
        if (isset($data['phone']) && $user->phone != $data['phone']) {
            if (User::wherePhone($data['phone'])->exists())
                return $this->createResponse((object)[], false, [
                    'code' => 400,
                    'message' => 'Phone must be unique'
                ]);

            $data['phone'] = $this->fixPhone($data['phone']);
            $data['isConfirm'] = false;

            $this->sendSMS($user->phone, $user->id);
        }

        User::whereToken($request->header('Authorization'))->update($data);

        $user = User::getUser($request->header('Authorization'))->toArray();

        unset($user['token']);

        return $this->createResponse(['result' => 'success', 'data' => $user]);
    }

    function allLogout(Request $request)
    {
        $user = User::getUser($request->header('Authorization'));

        $user->token = Str::random(20);
        $user->save();

        return $this->createResponse(['result' => 'success']);
    }

    function addAvatar(Request $request)
    {
        $user = User::getUser($request->header('Authorization'));

        $validatorResult = Validator::make($request->files->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg',
        ]);
        if($validatorResult->fails())
            return $this->responseError(Response::HTTP_BAD_REQUEST, 'Incorrect type of file');

        $file = $request->file('avatar');

        $path = '' . time() . '_' . Str::random(8) . '.' . strtolower($file->getClientOriginalExtension());
        $file->storeAs('', $path, 'public_uploads_user');

        $fileForDelete = User::whereToken($request->header('Authorization'))->value('avatar');
        if (!empty($fileForDelete) && file_exists(public_path('images/user/') . $fileForDelete)) {
            unlink(public_path('images/user/') . $fileForDelete);
        }

        $user->avatar = $path;
        $user->save();

        return $this->responseSuccess(['filename' => $path]);
    }

    function sendSecret($id)
    {
        $user = User::whereId($id)->firstOr(function () {
            Controller::throwError(Response::HTTP_NOT_FOUND, 'User not found');
        });
        $this->sendSMS($user->phone, $user->id);

        return $this->createResponse(['result' => 'success']);
    }

    function confirmPhone($id, $secret)
    {
        $user = User::whereId($id)->firstOr(function () {
            Controller::throwError(Response::HTTP_NOT_FOUND, 'User not found');
        });

        $code = VerifyCode::query()->where(['userId' => $id, 'secret' => $secret])->firstOr(function() {
            Controller::throwError(Response::HTTP_BAD_REQUEST, 'Secret error');
        });

        $code->forceDelete();
        $token = User::whereId($id)->value('token');

        $user->isConfirm = true;
        $user->save();

        return $this->createResponse(['token' => $token]);
    }

    function addAuto(Request $request)
    {
        $validatorResult = Validator::make($request->all(), [
            'make' => 'string',
            'model' => 'string',
            'type' => 'string',
            'modification' => 'string',
            'date' => 'integer',
            'vin' => 'string',
        ]);
        if($validatorResult->fails())
            return $this->responseError(0, json_encode($validatorResult->failed()));

        $user = User::getUser($request->header('Authorization'));

        $data = $validatorResult->validated();

        $data['pin'] = '0';
        $data['ownerId'] = $user->id;
        $data['created_at'] = date_create()->format('Y-m-d H:i:s');
        $data['updated_at'] = date_create()->format('Y-m-d H:i:s');

        $data['id'] = DB::table('user_devices')->insertGetId($data);
        return $this->createResponse($data);
    }

    function bindAutoToDevice($pin, $deviceId, Request $request) {
        $pin = Order::getPin($pin);
        $user = User::getUser($request->header('Authorization'));

//        if (!(is_numeric($pin) && (int) $pin >= (int) env('MIN_DEVICES_PIN') && (int) $pin < (int) env('MAX_DEVICES_PIN'))) {
//            return $this->createResponse((object) [], false, [
//                'code' => Response::HTTP_CONFLICT,
//                'message' => 'Device is not exists'
//            ]);
        if (DB::table('user_devices')->where(['pin' => $pin])->where('ownerId', '<>', $user->id)->exists()) {
            return $this->createResponse((object)[], false, [
                'code' => 403,
                'message' => 'It\'s not your device'
            ]);
        } elseif (!DB::table('user_devices')->where('id', $deviceId)->exists()) {
            return $this->createResponse((object)[], false, [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Auto is not exists'
            ]);
        } elseif (DB::table('user_devices')->where('id', $deviceId)
            ->where('pin', '<>', '0')->exists()) {
            return $this->createResponse((object)[], false, [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Auto has active device'
            ]);
        }

        $data = [
            'pin' => $pin,
            'updated_at' => date_create()->format('Y-m-d H:i:s')
        ];

        UserDevice::whereId($deviceId)->update($data);
        if (!Schema::hasTable('device_logs_' . $pin)) {
            Controller::sendRabbitMQMessage('pin_' . $pin, json_encode([
                'action' => 'include',
                'phone' => $user->phone
            ]));
        }

        return $this->createResponse(['result' => 'success']);
    }

    function deviceActivate($pin, Request $request) {
        $pin = Order::getPin($pin);
        $user = User::getUser($request->header('Authorization'));

        if (!Schema::hasTable('device_logs_' . $pin)) {
            Controller::sendRabbitMQMessage('pin_' . $pin, json_encode([
                'action' => 'include',
                'phone' => $user->phone
            ]));
        }
        return $this->createResponse(['result' => 'success']);
    }

    function addDevice($pin, Request $request)
    {
        $pin = Order::getPin($pin);
        $validatorResult = Validator::make($request->all(), [
            'make' => 'string',
            'model' => 'string',
            'type' => 'string',
            'modification' => 'string',
            'date' => 'integer',
            'vin' => 'string',
        ]);
        if($validatorResult->fails())
            return $this->responseError(0, json_encode($validatorResult->failed()));

        $user = User::getUser($request->header('Authorization'));

        UserDevice::checkNewDevice($pin, $user->id);

        $data = $validatorResult->validated();

        $data['ownerId'] = $user->id;
        $data['pin'] = $pin;
        $data['created_at'] = date_create()->format('Y-m-d H:i:s');
        $data['updated_at'] = date_create()->format('Y-m-d H:i:s');

        if (!DB::table('user_devices')->where('pin', $pin)->exists()) {
            DB::table('user_devices')->insert($data);
        }
        $this->deviceActivate($pin, $request);

        return $this->createResponse(['result' => 'success']);
    }

    function addDeviceAuto($pin, Request $request)
    {
        $pin = Order::getPin($pin);
        $user = User::getUser($request->header('Authorization'));

        UserDevice::checkNewDevice($pin, $user->id);

        $type = ['F', 'W', 'Y', 'Y', 'U'];
        $data = [
            'make' => DB::table('car_mark')
                ->orderBy(DB::raw('RAND()'))
                ->take(1)
                ->get()[0]->name,
            'model' => DB::table('car_model')
                ->orderBy(DB::raw('RAND()'))
                ->take(1)
                ->get()[0]->name,
            'type' => $type[rand(0, 4)].rand(1, 400),
            'modification' => DB::table('car_modification')
                ->orderBy(DB::raw('RAND()'))
                ->take(1)
                ->get()[0]->name,
            'date' => time() - 60 * 60 * 24 * 365 - rand(0, 60 * 60 * 24 * 365),
            'vin' => Str::random(12),
        ];
        $data['ownerId'] = $user->id;
        $data['pin'] = $pin;
        $data['created_at'] = date_create()->format('Y-m-d H:i:s');
        $data['updated_at'] = date_create()->format('Y-m-d H:i:s');

        UserDevice::query()->firstOrCreate(['pin' => $pin], $data);

        if (!Schema::hasTable('device_logs_' . $pin)) {
            Controller::sendRabbitMQMessage('pin_' . $pin, json_encode([
                'action' => 'include',
                'phone' => $user->phone
            ]));
        }

        return $this->createResponse(['result' => 'success']);
    }

    function updateDevice($pinOrId, Request $request)
    {
        $validatorResult = Validator::make($request->all(), [
            'make' => 'string',
            'model' => 'string',
            'type' => 'string',
            'modification' => 'string',
            'date' => 'integer',
            'vin' => 'string',
        ]);
        if($validatorResult->fails())
            return $this->responseError(0, json_encode($validatorResult->failed()));

        $user = User::getUser($request->header('Authorization'));

        if (UserDevice::query()->orWhere(['pin' => $pinOrId, 'id' => $pinOrId])
            ->where('ownerId', '<>', $user->id)->exists())
            return $this->responseError(Response::HTTP_FORBIDDEN, 'It\'s not your device');

        if (!UserDevice::query()->orWhere(['pin' => $pinOrId, 'id' => $pinOrId])->exists())
            return $this->responseError(Response::HTTP_NOT_FOUND, 'Device not found');

        $data = $validatorResult->validated();
        $data['updated_at'] = date_create()->format('Y-m-d H:i:s');

        UserDevice::query()->orWhere(['pin' => $pinOrId, 'id' => $pinOrId])->update($data);

        return $this->responseSuccess(['result' => 'success']);
    }

    function getDevicePaginate($page, $perPage, Request $request)
    {
        $user = User::getUser($request->header('Authorization'));

        if ($perPage == 0) {
            $userDevices = DB::table('user_devices')->where(['ownerId' => $user->id])->get();
        } else {
            $userDevices = DB::table('user_devices')->where(['ownerId' => $user->id])->forPage($page, $perPage)->get();
        }

        $data = [];
        foreach ($userDevices as $device) {
            if(!empty($device->pin)) {
                if (Schema::hasTable('device_logs_' . $device->pin)) {
                    if (DB::table('device_logs_' . $device->pin)->count() > 0) {
                        $deviceData = DB::table('device_logs_' . $device->pin)->latest()->first();
                        $deviceData->data = [
                            'TP' => $deviceData->TP,
                            'CL' => $deviceData->CL,
                            'OL' => $deviceData->OL,
                            'FL' => $deviceData->FL,
                            'BV' => $deviceData->BV,
                            'latitude' => $deviceData->latitude,
                            'longitude' => $deviceData->longitude,
                        ];
                        $device->data = $deviceData;
                    } else {
                        $device->data = null;
                    }
                } else {
                    $device->data = null;
                }
                $device->subscription = DB::table('subscriptions')->where('devicePin', $device->pin)->latest('id')->first();
            }
            array_push($data, $device);
        }

        return $this->createResponse($data);
    }

    function getDevice(Request $request)
    {
        return $this->getDevicePaginate(0, 0, $request);
    }

    function getDeviceData($pin, Request $request)
    {
        $pin = Order::getPin($pin);
        $user = User::getUser($request->header('Authorization'));

        if (UserDevice::wherePin($pin)->where('ownerId', '<>', $user->id)->exists())
            return $this->responseError(Response::HTTP_FORBIDDEN, 'It\'s not your device');

        $deviceData =
            DB::table('device_logs_' . $pin)
                ->whereDate('created_at', '>', date('Y-m-d H:i:s', (int)$_GET['timeStartS']))
                ->where('created_at', '<', date('Y-m-d H:i:s', (int)$_GET['timeEndS']))->get();

        for($i = 0; $i < count($deviceData); $i++) {
            $deviceData[$i]->data = [
                'TP' => $deviceData[$i]->TP,
                'CL' => $deviceData[$i]->CL,
                'OL' => $deviceData[$i]->OL,
                'FL' => $deviceData[$i]->FL,
                'BV' => $deviceData[$i]->BV,
                'latitude' => $deviceData[$i]->latitude,
                'longitude' => $deviceData[$i]->longitude,
            ];
        }

        return $this->createResponse($deviceData);
    }

    function getServicesPaginate($page, $perPage, Request $request)
    {
        $user = User::getUser($request->header('Authorization'));

        $table = Service::query();

        if ($request->get('text', '') != '') {
            $table->whereRaw("UPPER(serviceName) LIKE '%" . strtoupper($request->get('text')) . "%'");
        }
        if ($request->get('isFranchise', 'no') == 'yes' && $request->get('isIndependent', 'no') != 'yes') {
            $table->where('isOfficialDealer', true);
        } elseif ($request->get('isIndependent', 'no') == 'yes' && $request->get('isFranchise', 'no') != 'yes') {
            $table->where('isOfficialDealer', false);
        }

        if ($request->get('sort', '') != '') {
            $sortKey = $request->get('sort', '');
        } else if ($request->get('filter', '') != '') {
            $sortKey = $request->get('filter', '');
        } else {
            $sortKey = '';
        }

        if ($sortKey == 'name') {
            $table->orderBy('serviceName');
        } else {
            $table->orderBy('id');
        }

        if ($perPage == 0) {
            $data = $table->get();
        } else {
            $data = $table->forPage($page, $perPage)->get();
        }

        $latitude = (double)$request->get('latitude', 1000);
        $longitude = (double)$request->get('longitude', 1000);

        if (($latitude == 1000 || $longitude == 1000) && $request->get('filter', '') == 'distance') {
            $device = DB::table('user_devices')->where('ownerId', $user->id)->first();
            $deviceData = DB::table('device_logs_' . $device->pin)->latest()->first();
            $deviceData->data = json_decode($deviceData->data);

            $latitude = (double)$deviceData->data->latitude;
            $longitude = (double)$deviceData->data->longitude;
        }

        if ($sortKey == 'distance' && $latitude != 1000 && $longitude != 1000) {
            $data->sort(function ($a, $b) {
                if ($a->latitude * $a->latitude + $a->longitude * $a->longitude <
                    $b->latitude * $b->latitude + $b->longitude * $b->longitude) {
                    return 1;
                }
                return -1;
            });
        }

        $deviceIds = DB::table('user_devices')->where('ownerId', $user->id)->pluck('pin');

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]->isBusy = DB::table('orders')
                ->where(['serviceId' => $data[$i]->id])
                ->whereIn('status', [11])->exists();

            $data[$i]->orders = Order::query()->where(['serviceId' => $data[$i]->id])
                ->whereIn('devicePin', $deviceIds)
                ->whereIn('status', [1, 2, 7, 9])->with('device')->get();

            $data[$i]->freeTime = $this->getServiceFreeTime($data[$i]->id)->getData()->data;
            if ($data[$i]->description == null) {
                $data[$i]->description = '';
            }
        }

        return $this->createResponse($data);
    }

    function timeToInt($time) {
        if(count($time) > 2 && $time[2] == 'PM') {
            $time[0] = ((int) $time[0]) + 12;
            $time[1] = (int) $time[1];
        } else {
            $time[0] = (int) $time[0];
            $time[1] = (int) $time[1];
        }
        return $time;
    }

    function getServiceFreeTime($serviceId) {
        $weekdays = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        $serviceTime = Service::whereId($serviceId)->value('schedules');

        $freeTime = [];

        $date = new DateTime();
        $date->setTimestamp(time() + 60 * 30);
        $date->setTime((int) $date->format('H'), 0, 0);
        if ($date->getTimestamp() < time() + 60 * 30) {
            $date->setTime(1 + (int) $date->format('H'), 0, 0);
        }
        $startCalcTime = $date->getTimestamp();
        $startCalcTimeFixed = $date->getTimestamp();
        if(isset($_GET['timeFrom']) && (int) $_GET['timeFrom'] > time() + 60 * 30) {
            $startCalcTime = (int) $_GET['timeFrom'];
            $startCalcTimeFixed = (int) $_GET['timeFrom'];
        }

        $timeStartArray = Order::query()
            ->where('serviceId', $serviceId)
            ->where('timeStart', '>', $startCalcTime - 60 * 60)
            ->where('timeStart', '<', $startCalcTime - 60 * 60 + 60 * 60 * 24 * 7)->get();

        $isEnd = false;

        while (!$isEnd) {
            $skipRing = false;

            $timeInfo = getdate($startCalcTime);
            if($serviceTime['schedules'][$weekdays[$timeInfo['wday']]]['start'] != null && $serviceTime['schedules'][$weekdays[$timeInfo['wday']]]['end'] != null) {

                $start = preg_split('/[\s:]+/', $serviceTime['schedules'][$weekdays[$timeInfo['wday']]]['start']);
                $end = preg_split('/[\s:]+/', $serviceTime['schedules'][$weekdays[$timeInfo['wday']]]['end']);

                $start = $this->timeToInt($start);
                $end = $this->timeToInt($end);

                if($start[0] * 60 + $start[1] > $end[0] * 60 + $end[1]) {
                    $skipRing = true;
                }

                if(($timeInfo['hours'] + $serviceTime['timezoneOffset']) * 60 + $timeInfo['minutes'] < $start[0] * 60 + $start[1]
                    || ($timeInfo['hours'] + $serviceTime['timezoneOffset']) * 60 + $timeInfo['minutes'] > $end[0] * 60 + $end[1] - 40) {
                    $skipRing = true;
                }
            } else {
                $skipRing = true;
            }

            if($startCalcTime > $startCalcTimeFixed + 60 * 60 * 24 * 7) {
                $isEnd = true;
            }

            if(!$isEnd && !$skipRing) {
                $isWrongTime = false;
                foreach ($timeStartArray as $order) {
                    if ($startCalcTime > $order->timeStart - 40 * 60 && $startCalcTime < $order->timeStart + 40 * 60) {
                        $isWrongTime = true;
                    }
                }

                if (!$isWrongTime) {
                    array_push($freeTime, [
                        'timestamp' => $startCalcTime
                    ]);
                }
            }

            $startCalcTime += 60 * 20;
        }
        return $this->createResponse($freeTime);
    }

    function getServices(Request $request)
    {
        return $this->getServicesPaginate(0, 0, $request);
    }

    function getServicesList($serviceId) {
        return $this->responseSuccess(ServicesList::getServicesListByService($serviceId));
    }

    function getFullServicesListPaginate($page, $perPage) {
        $prepare = ServicesTemplate::query()->join('services_lists', function(JoinClause $join) {
            $join->on('services_templates.id', '=', 'services_lists.services_id');
        })->with('service')->with('group');
        if($perPage == 0) {
            $result = $prepare->get();
            return $this->responseSuccess($result);
        } else {
            $result = $prepare->forPage($page, $perPage)->get();
            return $this->responseSuccess($result);
        }
    }

    function getFullServicesList() {
        return $this->getFullServicesListPaginate(0, 0);
    }

    function getCatalogListPaginate($page, $perPage) {
        return $this->responseSuccess(ServicesTemplate::catalogList($page, $perPage));
    }

    function getCatalogList() {
        return $this->responseSuccess(ServicesTemplate::catalogList());
    }

    function getWorkshopsByIdFromCatalogPaginate($idFromCatalog, $page, $perPage) {
        return $this->responseSuccess(ServicesTemplate::workshopsByIdFromCatalog($idFromCatalog, $page, $perPage));
    }

    function getWorkshopsByIdFromCatalog($idFromCatalog) {
        return $this->responseSuccess(ServicesTemplate::workshopsByIdFromCatalog($idFromCatalog));
    }
}
