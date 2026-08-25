<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    private function broadcast($event): void
    {
        try {
            event($event);
        } catch (\Throwable $e) {
            Log::warning('Broadcast failed: '.$e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $users = User::where('id', '!=', auth()->id())
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%');
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
            'message' => 'nullable|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if (! $request->message && ! $request->hasFile('attachment')) {
            return redirect()->back()->with('error', 'Please enter a message or attach a file.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'is_read' => false,
        ]);

        $message->load(['sender', 'receiver']);

        $this->broadcast(new MessageSent($message));

        if (request()->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->toArray() + [
                    'sender' => ['id' => $message->sender->id, 'name' => $message->sender->name],
                    'attachment_url' => $attachmentPath ? Storage::url($attachmentPath) : null,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Message sent successfully ✅');
    }

    public function chat($id)
    {
        $receiver = User::findOrFail($id);

        Message::where('sender_id', $id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->broadcast(new MessageRead($id, auth()->id()));

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
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->broadcast(new MessageRead($id, auth()->id()));

        return response()->json(['success' => true]);
    }

    public function typing(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'is_typing' => 'required|boolean',
        ]);

        $this->broadcast(new UserTyping(
            auth()->id(),
            auth()->user()->name,
            $request->is_typing,
            $request->receiver_id
        ));

        return response()->json(['success' => true]);
    }

    public function editMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = Message::findOrFail($id);

        if ($message->sender_id != auth()->id()) {
            abort(403);
        }

        $message->update([
            'message' => $request->message,
            'edited_at' => now(),
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
            'is_deleted' => true,
        ]);

        return redirect()->back()->with('success', 'Message deleted successfully 🗑️');
    }
}
