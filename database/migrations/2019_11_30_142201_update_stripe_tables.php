<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStripeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stripe', function (Blueprint $table) {
            $table->dropColumn(['data']);
        });
        Schema::table('user_purchases', function(Blueprint $table) {
            $table->dropColumn(['data']);
        });
        Schema::table('stripe', function (Blueprint $table) {
            $table->longText('data');
        });
        Schema::table('user_purchases', function(Blueprint $table) {
            $table->longText('data')->nullable();
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
