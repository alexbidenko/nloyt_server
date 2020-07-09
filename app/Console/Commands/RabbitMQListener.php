<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// 6842

class RabbitMQListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'back:rabbit_mq_listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start RabbitMQ listener messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        while (true) {
            $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
            $channel = $connection->channel();

            $channel->queue_declare("reports", false, false, false, false);

            $callback = function(AMQPMessage $message){
                $messageBody = json_decode($message->body);

                if(!Schema::hasTable('device_logs_'.$messageBody->pin)) {
                    Schema::create('device_logs_' . $messageBody->pin, function (Blueprint $table) {
                        $table->bigIncrements('id');
                        $table->timestamps();

                        $table->integer('TP')->default(0)->nullable();
                        $table->integer('CL')->default(0)->nullable();
                        $table->integer('OL')->default(0)->nullable();
                        $table->integer('FL')->default(0)->nullable();
                        $table->integer('BV')->default(0)->nullable();
                        $table->integer('miliage')->default(0)->nullable();
                        $table->double('latitude')->default(0)->nullable();
                        $table->double('longitude')->default(0)->nullable();
                    });
                }

                $messageBody = json_decode($message->body);

                DB::table('device_logs_'.$messageBody->pin)->insert([
                    'created_at' => date_create()->format('Y-m-d H:i:s'),
                    'updated_at' => date_create()->format('Y-m-d H:i:s'),

                    'TP' => $messageBody->TP,
                    'CL' => $messageBody->CL,
                    'OL' => $messageBody->OL,
                    'FL' => $messageBody->FL,
                    'BV' => $messageBody->BV,
                    'miliage' => rand(34764, 137654),
                    'latitude' => $messageBody->latitude,
                    'longitude' => $messageBody->longitude,
                ]);
            };

            $channel->basic_consume("reports", '', false, true, false, false, $callback);

            while(count($channel->callbacks)) {
                try {
                    $channel->wait();
                } catch (\Exception $e) {}
            }

            $channel->close();
            try {
                $connection->close();
            } catch (\Exception $e) {}
        }
        return true;
    }
}
