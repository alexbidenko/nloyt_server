<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CarMark extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_mark', function (Blueprint $table) {
            $table->bigIncrements('id_car_mark');
            $table->string('name');
            $table->string('name_rus');
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
        Schema::dropIfExists('car_mark');
    }
}
