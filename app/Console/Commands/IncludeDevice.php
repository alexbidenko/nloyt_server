<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class IncludeDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:include_device {pin} {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Include device by pin';

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
        $connection = new AMQPStreamConnection(
            'rabbitmq',
            5672,
            'guest',
            'guest'
        );

        $channel = $connection->channel();
        $channel->queue_declare(
            'pin_'.$this->argument('pin'),
            false,
            false,
            false,
            false
        );

        $message = new AMQPMessage(json_encode([
            'action' => 'include',
            'phone' => $this->argument('phone')
        ]), array('delivery_mode' => 2));
        $channel->basic_publish($message, '', 'pin_'.$this->argument('pin'));

        $channel->close();
        try {
            $connection->close();
        } catch (\Exception $e) {}

        return true;
    }
}
