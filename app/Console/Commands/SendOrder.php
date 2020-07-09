<?php

namespace App\Console\Commands;

use App\Events\OrderWasReceived;
use Illuminate\Console\Command;

class SendOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:send {body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        event(new OrderWasReceived(['message' => $this->argument('body')]));
    }
}
