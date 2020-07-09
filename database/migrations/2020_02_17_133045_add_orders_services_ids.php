<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrdersServicesIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function(Blueprint $table) {
            $table->dropColumn(['type']);
            $table->jsonb('services_ids')->nullable();
        });
        $orders = DB::table('orders')->get();
        foreach ($orders as $order) {
            DB::table('orders')->where('id', $order->id)->update([
                'services_ids' => '[' . DB::table('services_lists')->where('service_id', $order->serviceId)->first()->id . ']'
            ]);
        }
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
