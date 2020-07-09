<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudyMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $from;
    public $toEntity;
    public $message;

    /**
     * Create a new event instance.
     * @param $message
     */
    public function __construct($message)
    {
        $this->id = $message['id'];
        $this->from = $message['from'];
        $this->toEntity = $message['toEntity'];
        $this->message = $message['message'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['messages'];
    }

    public function broadcastAs()
    {
        return 'study-message-'.$this->toEntity;
    }
}
