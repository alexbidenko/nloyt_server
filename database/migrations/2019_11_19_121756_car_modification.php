<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CarModification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_modification', function (Blueprint $table) {
            $table->bigIncrements('id_car_modification');
            $table->bigInteger('id_car_serie');
            $table->bigInteger('id_car_model');
            $table->string('name');
            $table->integer('start_production_year');
            $table->integer('end_production_year');
            $table->bigInteger('date_create');
            $table->bigInteger('date_update');
            $table->integer('id_car_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_modification');
    }
}
