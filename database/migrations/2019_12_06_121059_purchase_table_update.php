<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PurchaseTableUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn(['balance', 'lastSubscribe', 'typeSubscribe']);
            $table->bigInteger('subscribeBefore')->default(0);
        });
        Schema::table('students', function(Blueprint $table) {
            $table->string('patronymic')->nullable();
            $table->string('master')->nullable();
        });
        Schema::table('study_entities', function(Blueprint $table) {
            $table->string('schedule')->nullable();
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
