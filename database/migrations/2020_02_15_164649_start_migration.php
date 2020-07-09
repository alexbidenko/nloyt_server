<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StartMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at', 'name', 'serviceName', 'roles', 'serviceId', 'photo', 'position']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('fullName')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('photo')->nullable();
            $table->string('country')->nullable();
            $table->string('position')->nullable();
            $table->bigInteger('registrationTime')->nullable();
            $table->bigInteger('serviceId')->default(0);
            $table->jsonb('roles')->nullable();
            $table->boolean('isAdmin')->default(false);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at', 'adminId', 'serviceAddress', 'autoMarks', 'equipmentsAndSoftware', 'servicePhotos']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('servicePhone2')->nullable();
            $table->jsonb('autoMarks')->nullable();
            $table->jsonb('equipmentsAndSoftware')->nullable();
            $table->jsonb('servicePhotos')->nullable();
            $table->bigInteger('createTime')->nullable();
            $table->text('description')->nullable()->change();
            $table->dropColumn(['serviceTime']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('requestCode')->nullable();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->jsonb('address')->nullable();
            $table->jsonb('serviceTime')->nullable();
            $table->string('serviceAddress')->nullable();
            $table->dropColumn(['serviceTime']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->jsonb('schedules')->nullable();
            $table->string('serviceTime')->nullable();
        });

        Schema::table('user_devices', function(Blueprint $table) {
            $table->string('vin')->nullable();
        });

        Schema::create('order_receipts', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id');
            $table->bigInteger('employee_id');
            $table->bigInteger('create_time');
            $table->boolean('is_approved')->default(false);
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
