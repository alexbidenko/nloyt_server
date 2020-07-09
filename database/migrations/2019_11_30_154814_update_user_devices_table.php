<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUserDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_devices', function(Blueprint $table) {
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('modification')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('date')->nullable();
        });

        $devices = DB::table('user_devices')->get();
        foreach($devices as $device) {
            $parameters = json_decode($device->parameters, true);
            $parameters['date'] = (int) $parameters['date'];
            DB::table('user_devices')->where('id', $device->id)->update($parameters);
        }
        Schema::table('user_devices', function(Blueprint $table) {
            $table->dropColumn(['parameters']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
