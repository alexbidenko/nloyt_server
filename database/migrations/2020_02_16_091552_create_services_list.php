<?php

use App\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateServicesList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('service_id');
            $table->bigInteger('services_id');

        });
        Schema::create('services_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
        });
        Schema::create('services_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->jsonb('makes')->nullable();
            $table->jsonb('models')->nullable();
            $table->jsonb('gens')->nullable();
            $table->jsonb('modifications')->nullable();
            $table->jsonb('packages')->nullable();
            $table->jsonb('tools')->nullable();
            $table->string('photo')->nullable();
            $table->float('duration');
            $table->float('price');
            $table->bigInteger('services_group_id');
            $table->string('title');
            $table->text('description')->nullable();
        });

        $groupId = DB::table('services_groups')->insertGetId([
            'title' => 'Default'
        ]);

        $servicesListId = DB::table('services_templates')->insertGetId([
            'makes' => '["Volkswagen"]',
            'models' => '["E-Class"]',
            'gens' => '["W212"]',
            'modifications' => '["300 4Matic"]',
            'packages' => '["Luxury"]',
            'tools' => '["BOSCH KTS"]',
            'photo' => 'computer_diagnostic.jpg',
            'duration' => 40,
            'price' => 30,
            'services_group_id' => $groupId,
            'title' => 'Удаленная компьютерная диагностика'
        ]);

        $services = Service::all();

        foreach ($services as $service)
            DB::table('services_lists')->insert([
                'service_id' => $service->id,
                'services_id' => $servicesListId,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
