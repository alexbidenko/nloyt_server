<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceTesting extends Controller
{
    function sendMessage(Request $request) {
        $validates = Validator::make($request->all(), [
            'address' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        if ($validates->fails())
            return $this->responseError(0, $validates->failed());

        Controller::sendRabbitMQMessage(
            $validates->validated()['address'],
            $validates->validated()['message']
        );

        return $this->responseSuccess($request->all());
    }
}
