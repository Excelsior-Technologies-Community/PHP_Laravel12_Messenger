<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('id', '!=', auth()->id())
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->get();

        foreach ($users as $user) {
            $user->unread_count = Message::where('sender_id', $user->id)
                ->where('receiver_id', auth()->id())
                ->where('is_read', false)
                ->count();
        }

        return view('messenger.index', compact('users'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Message sent successfully ✅');
    }

    public function chat($id)
    {
        $receiver = User::findOrFail($id);

        // mark as read
        Message::where('sender_id', $id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);

        $messages = Message::where(function ($q) use ($id) {
                $q->where('sender_id', auth()->id())
                  ->where('receiver_id', $id);
            })
            ->orWhere(function ($q) use ($id) {
                $q->where('sender_id', $id)
                  ->where('receiver_id', auth()->id());
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view('messenger.chat', compact('messages', 'receiver'));
    }

    public function markAsRead($id)
    {
        Message::where('sender_id', $id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);

        return redirect()->back()->with('success', 'Messages marked as read 👀');
    }

    public function editMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $message = Message::findOrFail($id);

        if ($message->sender_id != auth()->id()) {
            abort(403);
        }

        $message->update([
            'message' => $request->message,
            'edited_at' => now()
        ]);

        return redirect()->back()->with('success', 'Message updated successfully ✏️');
    }

    public function deleteMessage($id)
    {
        $message = Message::findOrFail($id);

        if ($message->sender_id != auth()->id()) {
            abort(403);
        }

        $message->update([
            'is_deleted' => true
        ]);

        return redirect()->back()->with('success', 'Message deleted successfully 🗑️');
    }
}