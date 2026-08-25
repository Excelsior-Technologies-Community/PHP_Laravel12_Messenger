<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPresenceChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    public $userName;

    public $isOnline;

    public function __construct(int $userId, string $userName, bool $isOnline)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->isOnline = $isOnline;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('presence-online'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'is_online' => $this->isOnline,
        ];
    }
}
