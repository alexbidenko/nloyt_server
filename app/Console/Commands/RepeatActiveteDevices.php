<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RepeatActiveteDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:repeat_activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repeat activate';

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
     */
    public function handle()
    {
        $devices1 = DB::table('user_devices')->get()->toArray();
        $devices2 = DB::table('service_devices')->get()->toArray();

        $devices = array_merge($devices1, $devices2);

        foreach ($devices as $device) {
            $device = (array) $device;
            $connection = new AMQPStreamConnection(
                'rabbitmq',
                5672,
                'guest',
                'guest'
            );

            $channel = $connection->channel();
            $channel->queue_declare(
                'pin_' . $device['pin'],
                false,
                false,
                false,
                false
            );

            $message = new AMQPMessage(json_encode([
                'action' => 'include',
//                'phone' => DB::table('users')->where('id', $device->ownerId)->first()->phone
            ]), array('delivery_mode' => 2));
            $channel->basic_publish($message, '', 'pin_' . $device['pin']);

            $channel->close();
            try {
                $connection->close();
            } catch (\Exception $e) {
            }
        }

        return true;
    }
}
