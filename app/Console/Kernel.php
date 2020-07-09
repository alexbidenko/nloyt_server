<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use App\Events\OrderServiceEvent;
use App\Events\OrderWasReceived;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SendOrder::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function() {
            $orders = DB::table('orders')
                ->whereIn('status', [1, 9])
                ->where('timeStart', '<>', 0)
                ->where('timeStart', '<', time() - 60 * 30)->get();

            foreach($orders as $order) {
                $order->status = 10;
                $order->isStarted = false;
                $order->updated_at = date_create()->format('Y-m-d H:i:s');

                DB::table('orders')->where('id', $order->id)->update((array) $order);
                DB::table('order_history')->insert([
                    'orderId' => $order->id,
                    'status' => $order->status,
                    'timestamp' => time()
                ]);

                $device = DB::table('user_devices')->where('pin', $order->devicePin)->first();
                event(new OrderServiceEvent($order, $device));
                event(new OrderWasReceived($order, $device));
            }
        })->everyThirtyMinutes();

        $schedule->call(function() {
            $orders = DB::table('orders')
                ->where('status', 7)
                ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 5))->get();

            foreach($orders as $order) {
                $order->status = 8;
                $order->isStarted = false;
                $order->updated_at = date_create()->format('Y-m-d H:i:s');

                DB::table('orders')->where('id', $order->id)->update((array) $order);
                DB::table('order_history')->insert([
                    'orderId' => $order->id,
                    'status' => $order->status,
                    'timestamp' => time()
                ]);

                $device = DB::table('user_devices')->where('pin', $order->devicePin)->first();
                event(new OrderServiceEvent($order, $device));
                event(new OrderWasReceived($order, $device));
            }
        })->dailyAt('13:00');

        $schedule->call(function() {
            DB::table('verify_codes')->where('created_at', '<', date('Y-m-d H:i:s', time() - 60 * 60))->delete();
        })->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
