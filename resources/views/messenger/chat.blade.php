@extends('layouts.app')

@section('content')

<style>
    body {
        background: #0f172a;
    }

    .chat-wrapper {
        height: 92vh;
        background: linear-gradient(135deg, #0f172a, #1e293b);
    }

    .sidebar {
        background: rgba(15, 23, 42, 0.95);
        color: white;
        border-right: 1px solid rgba(255,255,255,0.1);
    }

    .chat-header {
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding: 15px 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
    }

    .avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #6366f1;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
    }

    .chat-area {
        flex: 1;
        overflow-y: auto;
        padding: 25px;
    }

    .message {
        max-width: 60%;
        padding: 10px 14px;
        border-radius: 16px;
        margin-bottom: 10px;
        position: relative;
    }

    .sender {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }

    .receiver {
        background: rgba(255,255,255,0.9);
        color: #111827;
        border-bottom-left-radius: 4px;
    }

    .time {
        font-size: 10px;
        opacity: 0.7;
        margin-top: 5px;
        display: block;
    }

    .seen { font-size: 10px; color: #34d399; }
    .edited { font-size: 10px; color: #fbbf24; }

    /* ACTIONS (EDIT DELETE) */
    .actions {
        display: none;
        margin-top: 6px;
    }

    .message:hover .actions {
        display: block;
    }

    .btn-action {
        font-size: 11px;
        border: none;
        background: transparent;
        color: #ddd;
        cursor: pointer;
        margin-right: 8px;
    }

    .btn-action:hover {
        color: #fff;
    }

    .chat-input {
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(10px);
        padding: 15px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .chat-input input {
        border-radius: 30px;
        height: 48px;
        border: none;
        padding-left: 18px;
    }

    .chat-input button {
        border-radius: 30px;
        padding: 0 25px;
    }

    /* TOAST */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .toast-box {
        background: #10b981;
        color: white;
        padding: 12px 18px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease;
        font-size: 14px;
    }

    @keyframes slideIn {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>

<!-- TOAST -->
<div class="toast-container">
    @if(session('success'))
        <div class="toast-box" id="toastBox">
            ✅ {{ session('success') }}
        </div>
    @endif
</div>

<div class="container-fluid chat-wrapper">

    <div class="row h-100">

        <!-- SIDEBAR -->
        <div class="col-md-3 sidebar p-4">
            <h4 class="fw-bold mb-4">💬 Messenger</h4>

            <a href="{{ route('messenger') }}" class="btn btn-light btn-sm">
                ← Back
            </a>
        </div>

        <!-- CHAT -->
        <div class="col-md-9 d-flex flex-column p-0">

            <!-- HEADER -->
            <div class="chat-header">
                <div class="avatar">
                    {{ strtoupper(substr($receiver->name,0,1)) }}
                </div>

                <div>
                    <h6 class="mb-0 fw-bold text-white">
                        {{ $receiver->name }}
                    </h6>
                    <small class="text-success">● Online</small>
                </div>
            </div>

            <!-- MESSAGES -->
            <div class="chat-area" id="chatArea">

                @forelse($messages as $msg)

                    @if(!$msg->is_deleted)

                        <div class="message {{ $msg->sender_id == auth()->id() ? 'sender' : 'receiver' }}">

                            {{ $msg->message }}

                            @if($msg->edited_at)
                                <div class="edited">(edited)</div>
                            @endif

                            <span class="time">
                                {{ $msg->created_at->format('h:i A') }}
                            </span>

                            {{-- ONLY OWNER CAN EDIT/DELETE --}}
                            @if($msg->sender_id == auth()->id())

                                <div class="actions">

                                    <!-- EDIT -->
                                    <form method="POST" action="{{ route('message.edit', $msg->id) }}">
                                        @csrf

                                        <input type="text"
                                               name="message"
                                               value="{{ $msg->message }}"
                                               style="width:100%;border-radius:8px;border:none;padding:4px;margin-top:5px;">

                                        <button class="btn-action">✏ Edit</button>
                                    </form>

                                    <!-- DELETE -->
                                    <form method="POST" action="{{ route('message.delete', $msg->id) }}">
                                        @csrf
                                        <button class="btn-action">🗑 Delete</button>
                                    </form>

                                </div>

                            @endif

                        </div>

                    @endif

                @empty
                    <div class="text-center text-white mt-5">
                        <h5>No messages yet</h5>
                    </div>
                @endforelse

            </div>

            <!-- INPUT -->
            <div class="chat-input">

                <form method="POST" action="{{ route('send.message') }}">
                    @csrf

                    <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">

                    <div class="input-group">

                        <input type="text"
                               name="message"
                               class="form-control"
                               placeholder="Type a message..."
                               required>

                        <button class="btn btn-primary">
                            Send
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<script>
    let chatArea = document.getElementById('chatArea');
    chatArea.scrollTop = chatArea.scrollHeight;

    let toast = document.getElementById('toastBox');

    if (toast) {
        setTimeout(() => toast.remove(), 2500);
    }
</script>

@endsection