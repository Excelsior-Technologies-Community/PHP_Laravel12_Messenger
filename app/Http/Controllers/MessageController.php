<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{

public function index()
{
    $users = User::all();   // current user include
    return view('messenger.index', compact('users'));
}

public function send(Request $request)
{
    Message::create([
        'sender_id' => Auth::id(),
        'receiver_id' => $request->receiver_id,
        'message' => $request->message,
    ]);

    return back();
}

public function chat($id)
{
    $receiver = \App\Models\User::findOrFail($id);

    $messages = \App\Models\Message::where(function($q) use ($id){
        $q->where('sender_id', auth()->id())
          ->where('receiver_id', $id);
    })->orWhere(function($q) use ($id){
        $q->where('sender_id', $id)
          ->where('receiver_id', auth()->id());
    })
    ->orderBy('created_at','asc')
    ->get();

    return view('messenger.chat', compact('messages','receiver'));
}


}
