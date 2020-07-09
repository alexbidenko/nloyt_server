<?php

use App\Service;
use App\ServiceDevice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateServiceDevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('service_devices')->delete();

        Schema::table('service_devices', function (Blueprint $table) {
            $table->dropColumn('ownerId');
            $table->bigInteger('service_id');
            $table->boolean('is_busy')->default(false);
            $table->string('connected_to')->nullable();
            $table->string('active_bridge')->nullable();
        });

        $pin = 223450;
        $services = Service::all();
        foreach ($services as $service) {
            $device = new ServiceDevice;
            $device->service_id = $service->id;
            $device->pin = $pin;
            $device->save();

            $pin++;
        }

        Schema::create('order_connection_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id');
            $table->string('user_device_pin');
            $table->string('service_device_pin');
            $table->string('bridge');
            $table->bigInteger('timestamp_start');
            $table->bigInteger('timestamp_end')->nullable();
            $table->text('message')->nullable();
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
