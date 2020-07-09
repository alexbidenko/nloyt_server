<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    function getAutos() {
        return $this->createResponse(DB::table('car_mark')
            ->join('car_model', 'car_mark.id_car_mark', '=', 'car_model.id_car_mark')
            ->join('car_modification', 'car_model.id_car_model', '=', 'car_modification.id_car_model')
            ->select('car_mark.name AS mark', 'car_mark.name_rus AS mark_rus',
                'car_model.name AS model', 'car_model.name_rus AS model_rus',
                'car_modification.start_production_year AS year')->get());
    }

    function getAutosByText($text) {
        return $this->createResponse(DB::table('car_mark')
            ->join('car_model', 'car_mark.id_car_mark', '=', 'car_model.id_car_mark')
            ->join('car_modification', 'car_model.id_car_model', '=', 'car_modification.id_car_model')
            ->select('car_mark.name AS mark', 'car_mark.name_rus AS mark_rus',
                'car_model.name AS model', 'car_model.name_rus AS model_rus',
                'car_modification.start_production_year AS year')
            ->orWhere('car_mark.name', 'LIKE', '%'.$text.'%')
            ->orWhere('car_mark.name_rus', 'LIKE', '%'.$text.'%')
            ->orWhere('car_model.name', 'LIKE', '%'.$text.'%')
            ->orWhere('car_model.name_rus', 'LIKE', '%'.$text.'%')->get());
    }

    function getMarks() {
        return $this->createResponse(DB::table('car_mark')->get());
    }

    function getModels() {
        return $this->createResponse(DB::table('car_model')->get());
    }

    function getModelsById($id) {
        return $this->createResponse(DB::table('car_model')->where('id_car_mark', $id)->get());
    }

    function getModifications() {
        return $this->createResponse(DB::table('car_modification')->get());
    }

    function getModificationsById($id) {
        return $this->createResponse(DB::table('car_modification')->where('id_car_model', $id)->get());
    }

    function getLocationByAddress($address) {
        $query = http_build_query([
            'searchtext' => $address,
            'app_id' => 'UdRH6PlISTlADYsW6mzl',
            'app_code' => 'lfrrTheP9nBedeJyy1NtIA',
            'gen' => '8'
        ]);
        $locationResponse = json_decode(file_get_contents('https://geocoder.api.here.com/6.2/geocode.json?'.$query), true);

        try {
            $location = $locationResponse["Response"]["View"][0]["Result"][0]["Location"]["DisplayPosition"];
        } catch (Exception $e) {
            return $this->createResponse((object)[], false, [
                'code' => 404,
                'message' => 'Address not found'
            ]);
        }

        return $this->createResponse(['latitude' => $location['Latitude'], 'longitude' => $location['Longitude']]);
    }
}
