<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('login');
            $table->string('password');
            $table->string('token');
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('avatar')->nullable();
            $table->integer('status')->nullable();
            $table->integer('entity')->nullable();
            $table->string('data')->nullable();
        });

        Schema::table('users', function(Blueprint $table) {
            $table->boolean('isActiveSubscribe')->default(false);
            $table->float('balance')->default(0);
            $table->bigInteger('lastSubscribe')->default(0);
            $table->string('typeSubscribe')->nullable();
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
