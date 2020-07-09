<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Nexmo\Client;
use Nexmo\Client\Credentials\Basic;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function createResponse($response, $isSuccess = true, $errors = []) {
        return response()->json([
            'data' => $response,
            'success' => $isSuccess,
            'error' => $errors
        ]);
    }

    function responseSuccess($message) {
        return response()->json([
            'data' => $message,
            'success' => true,
            'error' => []
        ]);
    }

    function responseError(int $code, $message) {
        return response()->json([
            'data' => [],
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ]
        ]);
    }

    static function throwError(int $code, $message) {
        Response::create([
            'data' => [],
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ]
        ], 200)->throwResponse();
    }

    function fixPhone($phone) {
        if(substr($phone, 0, 1) === '+') {
            $phone = ((string) (((int) substr($phone, 1, 1)) + 1)) . substr($phone, 2);
        }
        $phone = str_replace(['(', ')', '-', ' '], '', $phone);
        return $phone;
    }

    function sendSMS($phone, $id)
    {
        $phone = ((string)(((int)substr($phone, 0, 1)) - 1)) . substr($phone, 1);

        $basic = new Basic('96f989bf', 'sfAq03owjuGLr5Fe');
        $client = new Client($basic);
        $secretKey = (string)rand(1000, 9999);

        DB::table('verify_codes')->where('userId', $id)->delete();
        DB::table('verify_codes')->insert(
            ['userId' => $id, 'secret' => $secretKey, 'created_at' => date_create()->format('Y-m-d H:i:s')]
        );

        try {
            $message = $client->message()->send([
                'to' => $phone,
                'from' => 'NLOYT',
                'text' => 'Your verify code is : ' . $secretKey . '. for NLOYT service'
            ]);
            $response = $message->getResponseData();

            if ($response['messages'][0]['status'] == 0) {
                return $this->createResponse(['result' => $secretKey]);
            } else {
                return $this->createResponse(['result' => "The message failed with status: " . $response['messages'][0]['status']]);
            }
        } catch (Exception $e) {
        }
        return $this->createResponse(['result' => $e->getMessage()]);
    }

    /**
     * @param string $phone
     * @return string
     */
    function phoneToMaskAuto(string $phone) {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) > 0) {
            $firstChar = substr($phone, 0, 1);
            $phone = (((int) $firstChar) - 1).substr($phone, 1);
        }
        $codeIndex = -1;
        $codes = json_decode(file_get_contents(resource_path().'/json/phone_codes.json'), true);
        for ($i = 0; $i < count($codes); $i++) {
            $phoneCode = preg_replace('/\D/', '', $codes[$i]['mask']);
            if (strlen($phone) >= strlen($phoneCode) && $phoneCode == substr($phone, 0, strlen($phoneCode))) {
                $codeIndex = $i;
                break;
            } else if(strlen($phoneCode) >= strlen($phone) && $phone == substr($phoneCode, 0, strlen($phone))) {
                $codeIndex = $i;
                break;
            }
        }
        if ($codeIndex == -1 && strlen($phone) > 0) {
            return '+'.$phone;
        } else if ($codeIndex > -1) {
            $newPhone = '';
            $maskArray = str_split($codes[$i]['mask']);
            $isFirstBrake = false;
            $isSecondBrake = false;
            $nextIndex = -1;
            for ($i = 0; $i < count($maskArray); $i++) {
                if(strlen($phone) <= $nextIndex) break;
                $maskChar = $maskArray[$i];
                if ($maskChar == '+' || is_numeric($maskChar)) {
                    $newPhone .= $maskChar;
                    $nextIndex++;
                } else if($maskChar == ' ' && !$isFirstBrake) {
                    $newPhone .= ' (';
                    $isFirstBrake = true;
                } else if($maskChar == ' ' && !$isSecondBrake) {
                    $newPhone .= ') ';
                    $isSecondBrake = true;
                } else if($maskChar == ' ')
                    $newPhone .= '-';
                else {
                    $newPhone .= substr($phone, $nextIndex, 1);
                    $nextIndex++;
                }
            }
            return $newPhone;
        }
        return $phone;
    }

    static function sendRabbitMQMessage($address, $message) {
        $connection = new AMQPStreamConnection(
            'rabbitmq',
            5672,
            'guest',
            'guest'
        );

        $channel = $connection->channel();
        $channel->queue_declare(
            $address,
            false,
            false,
            false,
            false
        );

        $message = new AMQPMessage($message, array('delivery_mode' => 2));
        $channel->basic_publish($message, '', $address);

        $channel->close();
        try {
            $connection->close();
        } catch (\Exception $e) {}
    }
}
