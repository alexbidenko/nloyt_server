<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceBusyEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $serviceId;
    public $isBusy;
    public $activeOrdersCount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($serviceId, $isBusy, $activeOrdersCount)
    {
        $this->serviceId = $serviceId;
        $this->isBusy = $isBusy;
        $this->activeOrdersCount = $activeOrdersCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['service-channel'];
    }

    public function broadcastAs()
    {
        return 'service-is-busy-'.$this->serviceId;
    }
}
