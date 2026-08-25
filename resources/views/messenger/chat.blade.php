@extends('layouts.app')

@section('content')

<style>
    body { background: #f8fafc; }

    .chat-wrapper {
        height: 92vh;
        background: #f8fafc;
    }

    .sidebar {
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
    }

    .sidebar h4 { color: #1e293b; }

    .chat-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 15px 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #3b82f6;
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
        word-break: break-word;
    }

    .sender {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }

    .receiver {
        background: #f1f5f9;
        color: #1e293b;
        border-bottom-left-radius: 4px;
    }

    .time {
        font-size: 10px;
        opacity: 0.7;
        margin-top: 5px;
        display: block;
    }

    .tick {
        font-size: 11px;
        margin-left: 5px;
        color: #6474a5;
    }

    .tick.read { color: #3b82f6; }

    .edited { font-size: 10px; color: #f59e0b; }

    .attachment-preview {
        max-width: 200px;
        border-radius: 8px;
        margin-bottom: 5px;
        display: block;
        cursor: pointer;
    }

    .attachment-preview:hover {
        opacity: 0.9;
    }

    #typingIndicator {
        font-size: 12px;
        color: #94a3b8;
        min-height: 16px;
        margin-top: 5px;
    }

    .actions {
        display: none;
        margin-top: 6px;
    }

    .message:hover .actions { display: block; }

    .btn-action {
        font-size: 11px;
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        margin-right: 8px;
    }

    .btn-action:hover { color: #1e293b; }

    .chat-input {
        background: #ffffff;
        padding: 15px;
        border-top: 1px solid #e2e8f0;
    }

    .chat-input input[type="text"] {
        border-radius: 30px;
        height: 48px;
        border: 1px solid #cbd5e1;
        padding-left: 18px;
    }

    .chat-input button[type="submit"] {
        border-radius: 30px;
        padding: 0 25px;
        background: #3b82f6;
        border: none;
        color: white;
    }

    .chat-input button[type="submit"]:hover {
        background: #2563eb;
    }

    .attachment-btn {
        background: transparent;
        border: none;
        color: #6474a5;
        cursor: pointer;
        padding: 0 10px;
        font-size: 16px;
    }

    .attachment-btn:hover { color: #3b82f6; }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .toast-box {
        background: #22c55e;
        color: white;
        padding: 12px 18px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.3s ease;
        font-size: 14px;
    }

    .toast-error { background: #ef4444; }

    @keyframes slideIn {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .online-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    .online-dot.online { background: #22c55e; }
    .online-dot.offline { background: #94a3b5; }

    .preview-thumb {
        max-width: 80px;
        border-radius: 8px;
        margin-right: 8px;
        display: inline-block;
    }

    .empty-attachment {
        color: #94a3b8;
        font-size: 12px;
    }
</style>

<!-- TOAST -->
<div class="toast-container">
    @if(session('success'))
        <div class="toast-box" id="toastBox">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="toast-box toast-error" id="toastBox">
            ⚠️ {{ session('error') }}
        </div>
    @endif
</div>

<div class="container-fluid chat-wrapper">
    <div class="row h-100">

        <!-- SIDEBAR -->
        <div class="col-md-3 sidebar p-4">
            <h4 class="fw-bold mb-4">💬 Messenger</h4>

            <a href="{{ route('messenger') }}" class="btn btn-outline-secondary btn-sm">
                ← Back
            </a>
        </div>

        <!-- CHAT -->
        <div class="col-md-9 d-flex flex-column p-0">

            <!-- HEADER -->
            <div class="chat-header">
                <div class="avatar">
                    {{ strtoupper(substr($receiver->name, 0, 1)) }}
                </div>

                <div>
                    <h6 class="mb-0 fw-bold">{{ $receiver->name }}</h6>
                    <small id="receiverStatus">
                        <span class="online-dot" id="onlineDot"></span>
                        <span id="statusText">Checking...</span>
                    </small>
                </div>
            </div>

            <!-- MESSAGES -->
            <div class="chat-area" id="chatArea">

                @forelse($messages as $msg)

                    @if(!$msg->is_deleted)

                        <div class="message {{ $msg->sender_id == auth()->id() ? 'sender' : 'receiver' }}" data-msg-id="{{ $msg->id }}">

                            @if($msg->attachment_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($msg->attachment_path) }}" class="attachment-preview" alt="attachment">
                            @endif

                            {{ $msg->message }}

                            @if($msg->edited_at)
                                <div class="edited">(edited)</div>
                            @endif

                            <span class="time">
                                {{ $msg->created_at->format('h:i A') }}
                                @if($msg->sender_id == auth()->id())
                                    <span class="tick" id="tick-{{ $msg->id }}">
                                        @if($msg->is_read)
                                            ✓✓<span class="seen"> (Seen)</span>
                                        @else
                                            ✓
                                        @endif
                                    </span>
                                @endif
                            </span>

                            {{-- ONLY OWNER CAN EDIT/DELETE --}}
                            @if($msg->sender_id == auth()->id())
                                <div class="actions">
                                    <form method="POST" action="{{ route('message.edit', $msg->id) }}" class="d-inline">
                                        @csrf
                                        <input type="text" name="message" value="{{ $msg->message }}"
                                               style="width:100%;border-radius:8px;border:none;padding:4px;margin-top:5px;">
                                        <button class="btn-action">✏ Edit</button>
                                    </form>

                                    <form method="POST" action="{{ route('message.delete', $msg->id) }}" class="d-inline">
                                        @csrf
                                        <button class="btn-action">🗑 Delete</button>
                                    </form>
                                </div>
                            @endif

                        </div>

                    @endif

                @empty
                    <div class="text-center text-gray-400 mt-5">
                        <h5>No messages yet</h5>
                    </div>
                @endforelse

                <div id="typingIndicator"></div>

            </div>

            <!-- INPUT -->
            <div class="chat-input">
                <form id="messageForm" method="POST" action="{{ route('send.message') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">

                    <div class="input-group">
                        <label for="attachmentInput" class="attachment-btn" title="Attach image">
                            📎
                        </label>
                        <input type="file"
                               name="attachment"
                               id="attachmentInput"
                               accept="image/*"
                               class="d-none">

                        <input type="text"
                               name="message"
                               id="messageInput"
                               class="form-control"
                               placeholder="Type a message...">

                        <button type="submit" class="btn btn-primary">
                            Send
                        </button>
                    </div>

                    <div id="attachmentPreviewContainer" class="mt-2" style="display:none;">
                        <img id="previewThumb" class="preview-thumb" src="" alt="preview">
                        <span class="empty-attachment" id="previewName"></span>
                        <button type="button" id="removeAttachment" class="btn btn-sm btn-link text-danger p-0 ms-2">Remove</button>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

<script>
    const chatArea = document.getElementById('chatArea');
    if (chatArea) {
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    const toast = document.getElementById('toastBox');
    if (toast) {
        setTimeout(() => toast.remove(), 2500);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Attachment preview
    const attachmentInput = document.getElementById('attachmentInput');
    const previewContainer = document.getElementById('attachmentPreviewContainer');
    const previewThumb = document.getElementById('previewThumb');
    const previewName = document.getElementById('previewName');
    const removeBtn = document.getElementById('removeAttachment');

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (ev) {
                    previewThumb.src = ev.target.result;
                    previewName.textContent = file.name;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            attachmentInput.value = '';
            previewContainer.style.display = 'none';
            previewThumb.src = '';
            previewName.textContent = '';
        });
    }

    // AJAX form submission
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const messageInput = document.getElementById('messageInput');
            const attachment = attachmentInput.files[0];

            if (!messageInput.value.trim() && !attachment) {
                alert('Please type a message or attach an image.');
                return;
            }

            fetch('{{ route('send.message') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success && data.message) {
                    const chatArea = document.getElementById('chatArea');
                    if (chatArea) {
                        const div = document.createElement('div');
                        div.className = 'message sender';
                        let html = '';
                        if (data.message.attachment_url) {
                            html += '<img src="' + data.message.attachment_url + '" class="attachment-preview" alt="attachment">';
                        }
                        html += (data.message.message || '');
                        html += '<span class="time">' + data.message.created_at + ' <span class="tick">✓</span></span>';
                        div.innerHTML = html;
                        chatArea.appendChild(div);
                        chatArea.scrollTop = chatArea.scrollHeight;
                    }

                    messageInput.value = '';
                    attachmentInput.value = '';
                    previewContainer.style.display = 'none';
                    previewThumb.src = '';
                    previewName.textContent = '';
                }
            })
            .catch(() => {
                alert('Failed to send message. Please try again.');
            });
        });
    }

    // Initialize real-time messenger
    if (typeof window.messengerInit === 'function') {
        window.messengerInit({
            currentUserId: {{ auth()->id() }},
            receiverId: {{ $receiver->id }},
            csrfToken: csrfToken,
        });
    }

    // Update receiver status from presence channel
    function updateReceiverStatus() {
        const onlineDot = document.getElementById('onlineDot');
        const statusText = document.getElementById('statusText');
        if (!onlineDot || !statusText) return;

        const userId = {{ $receiver->id }};
        if (window.messengerOnlineUsers && window.messengerOnlineUsers[userId]) {
            onlineDot.classList.remove('offline');
            onlineDot.classList.add('online');
            statusText.textContent = 'Online';
        } else {
            onlineDot.classList.remove('online');
            onlineDot.classList.add('offline');
            statusText.textContent = 'Offline';
        }
    }

    setInterval(updateReceiverStatus, 3000);
    setTimeout(updateReceiverStatus, 1000);
</script>

@endsection
