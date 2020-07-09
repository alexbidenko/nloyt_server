<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('devicePin');
            $table->double('amount');
            $table->bigInteger('subscriptionStart');
            $table->bigInteger('subscriptionBefore');
            $table->bigInteger('timestamp');
        });
        Schema::table('user_purchases', function(Blueprint $table) {
            $table->dropColumn(['target']);
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn(['subscribeBefore', 'isActiveSubscribe']);
        });
        Schema::table('orders', function(Blueprint $table) {
            $table->dropColumn(['files']);
        });
        Schema::create('order_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('serviceId');
            $table->bigInteger('orderId');
            $table->bigInteger('timestamp');
            $table->string('filename');
        });
        Schema::create('order_conclusions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('serviceId');
            $table->bigInteger('orderId');
            $table->bigInteger('timestamp');
            $table->string('text');
            $table->integer('risk');
        });
        /*
         * {
"id": 2,
"isActiveSubscribe": true,
"subscribeBefore": 1607677463,
"paymentMethod": null
},
{
"id": 4,
"isActiveSubscribe": true,
"subscribeBefore": 1578707345,
"paymentMethod": null
}
         */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
