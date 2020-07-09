<?php

namespace App\Http\Controllers;

use App\Events\StudyMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StudyController extends Controller
{
    function authorization(Request $request) {
        if(DB::table('students')->where(['login' => $request->all()['login'], 'password' => $request->all()['password']])->exists()) {
            $user = DB::table('students')->where(['login' => $request->all()['login'], 'password' => $request->all()['password']])->first();

            if($user->data != null && $user->data != '') {
                $data = array_chunk(preg_split("/[:;]/", $user->data), 2);
                $table = DB::table('study_entities');
                foreach ($data as $entity) {
                    $table->orWhere('id', $entity[1]);
                }
                $user->entities = $table->get();
            } else {
                $user->entities = [];
            }

            return response()->json($user);
        } else {
            return response()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }

    function getLastMessage(Request $request) {
        if(DB::table('students')->where(['token' => $request->header('Authorization')])->exists()) {
            $user = DB::table('students')->where(['token' => $request->header('Authorization')])->first();

            if($user->status > 1) {
                return response()->setStatusCode(Response::HTTP_CONFLICT);
            }

            $data = array_chunk(preg_split("/[:;]/", $user->data), 2);

            $table = DB::table('messages');
            foreach ($data as $entity) {
                $table->orWhere('toEntity', $entity[1]);
            }

            if($table->exists()) {
                $message = $table->latest('id')->first();
            } else {
                return response()->setStatusCode(Response::HTTP_NOT_FOUND);
            }

            return response()->json($message);
        } else {
            return response()->setStatusCode(Response::HTTP_FORBIDDEN);
        }
    }

    function sendMessage(Request $request) {
        if(DB::table('students')->where(['token' => $request->header('Authorization')])->where('status', '>', 1)->exists()) {
            $user = DB::table('students')->where(['token' => $request->header('Authorization')])->first();

            $newMessage = [
                'from' => $user->id,
                'toEntity' => $request->all()['toEntity'],
                'message' => $request->all()['message']
            ];
            $newMessage['id'] = DB::table('messages')->insertGetId($newMessage);

            $table = DB::table('study_entities')->where('id', $request->all()['toEntity'])->get()->toArray();

            $length = 1;
            for ($i = 0; $i < $length; $i++) {
                $row = $table[$i];
                foreach ($this->getEntitiesById($row->id) as $rowMore) {
                    array_push($table, $rowMore);
                    $length++;
                }
            }
            foreach ($table as $row) {
                if($row->subId != null) {
                    $newMessage['toEntity'] = $row->id;
                    event(new StudyMessage($newMessage));
                }
            }

            return response()->json($newMessage);
        } else {
            return response()->setStatusCode(Response::HTTP_FORBIDDEN);
        }
    }

    function getEntitiesById($id) {
        return DB::table('study_entities')->where('subId', $id)->get()->toArray();
    }

    function getEntities(Request $request) {
        if(DB::table('students')->where(['token' => $request->header('Authorization')])->exists()) {
            $user = DB::table('students')->where(['token' => $request->header('Authorization')])->first();

            if($user->status == 1) {
                return response()->setStatusCode(Response::HTTP_CONFLICT);
            }

            $data = array_map(function ($el) {
                    return $el[1];
                },
                array_chunk(preg_split("/[:;]/", $user->master), 2)
            );

            $table = DB::table('study_entities')->whereIn('id', $data)->get()->toArray();

            $length = 1;
            for ($i = 0; $i < $length; $i++) {
                $row = $table[$i];
                foreach ($this->getEntitiesById($row->id) as $rowMore) {
                    array_push($table, $rowMore);
                    $length++;
                }
            }

            return response()->json($table);
        } else {
            return response()->setStatusCode(Response::HTTP_FORBIDDEN);
        }
    }
}
