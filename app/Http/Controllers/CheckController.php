<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Orders\Order;
use App\Service;
use App\ServicesList;
use App\ServicesTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckController extends Controller
{

    function getTable($table) {
        return response()->json(DB::table($table)->get());
        switch ($table) {
            case 'services':
                return Service::all();
            case 'employees':
                return Employee::all();
            case 'orders':
                return Order::all();
            case 'services_lists':
                return ServicesList::all();
            case 'services_templates':
                return ServicesTemplate::all();
            default:
                return response()->json(DB::table($table)->get());
        }
    }

    function deleteTable($table) {
        return response()->json(DB::table($table)->delete());
    }

    function deleteFullTable($table) {
        Schema::dropIfExists($table);
        return response()->json(['result' => 'success']);
    }

    function insertTable($table, Request $request) {
        $data = $request->all();
        foreach ($data as $row) {
            DB::table($table)->updateOrInsert($row);
        }
        return response()->json(DB::table($table)->get());
    }

    function checkPost(Request $request) {
        return file_get_contents('php://input') .
            '|||' . var_dump($_POST) . '|||' . json_encode($request->all());
    }
}
