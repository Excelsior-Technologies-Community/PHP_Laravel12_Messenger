<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId;
    public $readerId;

    public function __construct(int $senderId, int $readerId)
    {
        $this->senderId = $senderId;
        $this->readerId = $readerId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-messenger.'.$this->senderId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'reader_id' => $this->readerId,
            'sender_id' => $this->senderId,
        ];
    }
}
