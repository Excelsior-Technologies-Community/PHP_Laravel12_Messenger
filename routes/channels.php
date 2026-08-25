<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('private-messenger.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId || true;
});

Broadcast::channel('presence-online', function ($user) {
    if ($user) {
        $user->update(['last_seen' => now()]);

        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
}, ['guards' => ['web']]);
