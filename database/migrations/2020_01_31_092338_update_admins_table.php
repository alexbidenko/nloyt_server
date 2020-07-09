<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('lastName')->nullable();
            $table->string('firstName')->nullable();
            $table->bigInteger('registrationTime')->nullable();
        });
        $admins = DB::table('admins')->get();
        foreach ($admins as $admin) {
            DB::table('admins')->where('id', $admin->id)->update([
                'firstName' => $admin->name,
                'lastName' => ' ',
                'registrationTime' => strtotime($admin->created_at),
            ]);
        }
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['name', 'created_at', 'updated_at', 'number']);
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
