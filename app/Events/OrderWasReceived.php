<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderWasReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $device;

    /**
     * Create a new event instance.
     * @param $order
     */
    public function __construct($order, $device)
    {
        $this->order = (array) $order;
        $this->device = (array) $device;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['orders'];
    }

    public function broadcastAs()
    {
        return 'order-'.$this->order['devicePin'];
    }
}
