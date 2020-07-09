<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Service;
use App\VerifyCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{

    function addAdmin(Request $request) {
        $validatorResult = Validator::make($request->all(), [
            'phone' => 'required',
            'country' => 'required|string',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|string',
            'code' => 'required|numeric',
        ]);
        if($validatorResult->fails())
            return $this->responseError(0, json_encode($validatorResult->failed()));

        $newData = $validatorResult->validated();

        if(Employee::wherePhone($newData['phone'])->exists())
            return $this->responseError(Response::HTTP_FORBIDDEN, 'User exists');

        if(!VerifyCode::whereSecret($newData['code'])->exists())
            return $this->responseError(Response::HTTP_BAD_REQUEST, 'Code not exists');

        VerifyCode::whereSecret($newData['code'])->forceDelete();

        $passwordKey = Str::random(20);
        $newData['password'] = 'empty:' . $passwordKey;
        $newData['token'] = Str::random(20);
        $newData['phone'] = $this->fixPhone($newData['phone']);
        $newData['isAdmin'] = true;

        $newData['registrationTime'] = time();

        unset($newData['code']);
        $newData['id'] = DB::table('employees')->insertGetId($newData);
        unset($newData['password'] );
        unset($newData['token'] );

        if ($request->header('Language') == 'ru-RU') {
            $title = __('emails.set_password.title', [], 'ru');
            $message = __('emails.set_password.message', [], 'ru');
        } else {
            $title = __('emails.set_password.title', [], 'en');
            $message = __('emails.set_password.message', [], 'en');
        }
        $message .= 'http://194.182.85.89/password?key=' . $passwordKey;

        $url = 'https://admire.social/sendMail.php';
        $data = array(
            'address' => $newData['email'],
            'subject' => $title,
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

        return $this->responseSuccess($newData);
    }

    function sendPhoneCode(Request $request) {
        $validatorResult = Validator::make($request->all(), [
            'phone' => 'required',
        ]);
        if($validatorResult->fails())
            return $this->responseError(0, json_encode($validatorResult->failed()));

        if(Employee::wherePhone($request->phone)->exists())
            return $this->responseError(Response::HTTP_FORBIDDEN, 'User exists');

        return $this->sendSMS($request->all()['phone'], 0);
    }

    function setPassword(Request $request) {
        $admin = Employee::wherePassword('empty:'.$request->header('Password-Key'))->firstOr(function () {
            Controller::throwError(Response::HTTP_NOT_FOUND, 'User not found');
        });

        $admin->password = Hash::make($request->all()['password']);
        $admin->save();

        return $this->responseSuccess($admin);
    }

    function loginAdmin(Request $request) {
        $validatorResult = Validator::make($request->all(), [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        if($validatorResult->fails())
            return $this->responseError(0, json_encode($validatorResult->failed()));

        $data = $validatorResult->validated();

        $user = Employee::getUser($data['email']);

        if (!Hash::check($request->all()['password'], $user->password))
            return $this->createResponse(Response::HTTP_FORBIDDEN, 'Not correct password');

        return $this->responseSuccess($user);
    }

    function addService(Request $request) {
        $admin = Employee::getEmployee($request->header('Authorization'));

        $newData = $request->all();

        $filePaths = [];
        for ($i = 0; $i < (int) $newData['servicePhotosCount']; $i++) {
            $file = $request->file('servicePhotos'.$i);
            $path = ''.time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            array_push($filePaths, $path);
            $file->storeAs('', $path, 'public_uploads_service');
            unset($newData['servicePhotos'.$i]);
        }
        $newData['servicePhotos'] = json_encode($filePaths);
        unset($newData['servicePhotosCount']);

        $newData['servicePhone'] = $this->fixPhone($newData['servicePhone']);

        $newData['createTime'] = time();

        $newData['address'] = '';
        foreach ($newData['serviceAddress'] as $text) {
            if ($newData['address'] != '') {
                $newData['address'] .= ', ';
            }
            $newData['address'] .= $text;
        }

        $newData['id'] = DB::table('services')->insertGetId($newData);
        $admin->serviceId = $newData['id'];
        $admin->save();

        return $this->createResponse($newData);
    }
}
