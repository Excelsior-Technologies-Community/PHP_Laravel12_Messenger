@extends('layouts.app')

@section('content')

<style>
    .chat-wrapper {
        height: 92vh;
        background: linear-gradient(135deg, #eef2ff, #f8fafc);
    }

    .sidebar {
        background: #111827;
        color: white;
    }

    .chat-header {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 15px 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
    }

    .avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #4f46e5;
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

    .sender-message {
        background: #4f46e5;
        color: white;
        border-radius: 18px 18px 5px 18px;
        padding: 12px 16px;
        max-width: 65%;
        box-shadow: 0 3px 10px rgba(79, 70, 229, .2);
    }

    .receiver-message {
        background: white;
        color: #111827;
        border-radius: 18px 18px 18px 5px;
        padding: 12px 16px;
        max-width: 65%;
        box-shadow: 0 3px 10px rgba(0, 0, 0, .08);
    }

    .chat-input {
        background: white;
        padding: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .chat-input input {
        border-radius: 50px;
        height: 50px;
    }

    .send-btn {
        border-radius: 50px;
        padding: 0 30px;
    }

    .time {
        font-size: 11px;
        opacity: .8;
    }

    .seen {
        font-size: 11px;
        color: #86efac;
    }

    .back-btn {
        border-radius: 25px;
    }
</style>

<div class="container-fluid chat-wrapper">

    <div class="row h-100">

        <!-- Sidebar -->
        <div class="col-md-3 sidebar p-4">

            <h3 class="fw-bold mb-4">
                Laravel Messenger
            </h3>

            <a href="{{ route('messenger') }}"
                class="btn btn-light back-btn">
                ← Back
            </a>

        </div>

        <!-- Chat Section -->
        <div class="col-md-9 d-flex flex-column p-0">

            <!-- Header -->
            <div class="chat-header">

                <div class="avatar">
                    {{ strtoupper(substr($receiver->name,0,1)) }}
                </div>

                <div>
                    <h5 class="mb-0 fw-bold">
                        {{ $receiver->name }}
                    </h5>

                    <small class="text-success">
                        ● Active Chat
                    </small>
                </div>

            </div>

            <!-- Messages -->
            <div class="chat-area" id="chatArea">

                @forelse($messages as $msg)

                @if($msg->sender_id == auth()->id())

                <div class="d-flex justify-content-end mb-3">

                    <div class="sender-message">

                        {{ $msg->message }}

                        <div class="text-end mt-2">

                            <span class="time">
                                {{ $msg->created_at->format('h:i A') }}
                            </span>

                            <br>

                            @if($msg->is_read)
                            <span class="seen">
                                ✓✓ Seen
                            </span>
                            @else
                            <span class="time">
                                ✓ Sent
                            </span>
                            @endif

                        </div>

                    </div>

                </div>

                @else

                <div class="d-flex justify-content-start mb-3">

                    <div class="receiver-message">

                        {{ $msg->message }}

                        <div class="mt-2">

                            <span class="text-muted time">
                                {{ $msg->created_at->format('h:i A') }}
                            </span>

                        </div>

                    </div>

                </div>

                @endif

                @empty

                <div class="text-center mt-5">

                    <img src="https://cdn-icons-png.flaticon.com/512/1041/1041916.png"
                        width="90">

                    <h5 class="mt-3 text-muted">
                        No messages yet
                    </h5>

                    <p class="text-secondary">
                        Start the conversation now.
                    </p>

                </div>

                @endforelse

            </div>

            <!-- Input -->
            <div class="chat-input">

                <form method="POST"
                    action="{{ route('send.message') }}">

                    @csrf

                    <input type="hidden"
                        name="receiver_id"
                        value="{{ $receiver->id }}">

                    <div class="input-group">

                        <input type="text"
                            name="message"
                            class="form-control"
                            placeholder="Type your message..."
                            required>

                        <button class="btn btn-primary send-btn">
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
</script>

@endsection