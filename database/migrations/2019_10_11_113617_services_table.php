<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('adminId');
            $table->string('legalEntityName');
            $table->string('legalEntityNumber');
            $table->boolean('isOfficialDealer');
            $table->boolean('isInHolding');
            $table->string('holdingName');
            $table->string('holdingSite');
            $table->string('serviceName');
            $table->string('serviceAddress');
            $table->string('servicePhone');
            $table->string('serviceTime');
            $table->string('serviceSite');
            $table->string('autoMarks');
            $table->string('equipmentsAndSoftware');
            $table->string('servicePhotos');
            $table->string('description');
            $table->integer('receiversCount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
