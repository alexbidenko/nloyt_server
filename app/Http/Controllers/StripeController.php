<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe\Charge;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripeController extends Controller
{
    function stripeEndpoint(Request $request) {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $event = null;

        try {
            $event = Event::constructFrom(
                $request->all()
            );
        } catch(\UnexpectedValueException $e) {
            http_response_code(400);
            exit();
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
            case 'payment_intent.canceled':
            case 'payment_intent.created':
            case 'payment_intent.payment_failed':
            case 'payment_intent.amount_capturable_updated':
            case 'payment_method.attached':
                DB::table('stripe')->insert([
                    'stripeId' => $event->id,
                    'amount' => $event->data->object->amount,
                    'type' => $event->data->object->type,
                    'data' => json_encode($event->data->object),
                    'created_at' => date_create()->format('Y-m-d H:i:s'),
                    'updated_at' => date_create()->format('Y-m-d H:i:s')
                ]);
                break;
            default:
                http_response_code(400);
                exit();
        }

        http_response_code(200);
    }

    function addPurchase(Request $request) {
        $validatorResult = Validator::make($request->all(), [
            'stripeToken' => 'bail|required',
            'amount' => 'required|min:50|numeric',
            'devicePin' => 'required',
            'paymentPeriod' => 'required'
        ]);
        if($validatorResult->fails()) {
            return $this->createResponse((object) [], false, [
                'code' => 0,
                'message' => json_encode($validatorResult->failed())
            ]);
        }

        if(!DB::table('users')->where(['token' => $request->header('Authorization')])->exists()) {
            return $this->createResponse((object) [], false, [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'User not found'
            ]);
        }

        $user = DB::table('users')->where(['token' => $request->header('Authorization')])->first();
        if(!DB::table('user_devices')->where(['ownerId' => $user->id, 'pin' => $request->all()['devicePin']])->exists()) {
            return $this->createResponse((object) [], false, [
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'User not found'
            ]);
        }

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $charge = Charge::create([
                "amount" => ((float) $request->all()['amount']) * 100,
                "currency" => "usd",
                "source" => $request->all()['stripeToken']
            ]);
        } catch (ApiErrorException $e) {
            return $this->createResponse((object) [], false, [
                'code' => 403,
                'message' => $e->getMessage()
            ]);
        }

        DB::table('user_purchases')->insert([
            'stripeId' => $request->all()['stripeToken'],
            'userId' => $user->id,
            'amount' => $request->all()['amount'],
            'data' => json_encode($charge),
            'created_at' => date_create()->format('Y-m-d H:i:s'),
            'updated_at' => date_create()->format('Y-m-d H:i:s')
        ]);

        if(DB::table('subscriptions')->where('devicePin', $request->all()['devicePin'])
            ->where('subscriptionBefore', '>', time())->exists()) {
            $lastSubscription = DB::table('subscriptions')->where('devicePin', $request->all()['devicePin'])
                ->where('subscriptionBefore', '>', time())->latest('id')->first();
            $newSubscribeStart = $lastSubscription->subscriptionBefore;
            $newSubscribeBefore = $newSubscribeStart + 60 * 60 * 24 * 30.5 * ((float) $request->all()['paymentPeriod']);
        } else {
            $newSubscribeStart = time();
            $newSubscribeBefore = $newSubscribeStart + 60 * 60 * 24 * 30.5 * ((float) $request->all()['paymentPeriod']);
        }

        $subscription = [
            'devicePin' => $request->all()['devicePin'],
            'amount' => $request->all()['amount'],
            'subscriptionStart' => $newSubscribeStart,
            'subscriptionBefore' => $newSubscribeBefore,
            'timestamp' => time()
        ];
        $subscription['id'] = DB::table('subscriptions')->insertGetId($subscription);

        return $this->createResponse($subscription);
    }
}
