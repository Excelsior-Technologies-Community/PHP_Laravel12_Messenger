@extends('layouts.app')

@section('content')

<style>
    .messenger-wrapper {
        min-height: 92vh;
        background: #f8fafc;
    }

    .user-sidebar {
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        overflow-y: auto;
    }

    .user-sidebar h4 {
        color: #1e293b;
    }

    .user-item {
        background: #f8fafc;
        padding: 12px;
        border-radius: 12px;
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        text-decoration: none;
    }

    .user-item:hover {
        background: #e2e8f0;
        transform: translateX(3px);
    }

    .avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        margin-right: 10px;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 500;
        color: #1e293b;
    }

    .user-status {
        font-size: 11px;
        color: #94a3b5;
    }

    .badge-custom {
        background: #ef4444;
        font-size: 12px;
        padding: 5px 8px;
        border-radius: 20px;
    }

    .empty-state {
        text-align: center;
        color: #94a3b5;
    }

    .online-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }

    .online-dot.online { background: #22c55e; }
    .online-dot.offline { background: #94a3b5; }
</style>

<div class="container-fluid messenger-wrapper">
    <div class="row h-100">

        <!-- Sidebar -->
        <div class="col-md-4 col-lg-3 user-sidebar p-3">

            <h4 class="fw-bold mb-3">
                💬 Messenger
            </h4>

            <!-- Search -->
            <form method="GET" action="{{ route('messenger') }}" class="mb-3">
                <input type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search users..."
                    value="{{ request('search') }}"
                    style="border-radius:20px;">
            </form>

            <!-- Users -->
            @forelse($users as $user)
            <a href="{{ route('chat', $user->id) }}"
                class="text-decoration-none text-white user-link"
                data-user-id="{{ $user->id }}">

                <div class="user-item">
                    <div class="d-flex align-items-center">
                        <div class="avatar">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ $user->name }}</div>
                            <div class="user-status" id="status-{{ $user->id }}">
                                <span class="online-dot offline"></span>Offline
                            </div>
                        </div>
                    </div>

                    <span class="badge-custom" id="badge-{{ $user->id }}" style="display: none;">
                        0
                    </span>
                </div>
            </a>
            @empty
            <div class="empty-state mt-4">
                <h6>No users found</h6>
            </div>
            @endforelse
        </div>

        <!-- Right Side -->
        <div class="col-md-8 col-lg-9 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/1041/1041916.png"
                    width="90" class="mb-3">

                <h4 class="text-muted">Select a conversation</h4>

                <p class="text-secondary">
                    Choose a user from the left sidebar to start chatting.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    const currentUserId = {{ auth()->id() }};

    // Set up unread counts from server data
    @foreach($users as $user)
    @if($user->unread_count > 0)
    document.getElementById('badge-{{ $user->id }}').style.display = 'inline-block';
    document.getElementById('badge-{{ $user->id }}').textContent = {{ $user->unread_count }};
    @endif
    @endforeach

    // Listen for new messages in real-time to update unread badge
    if (window.Echo) {
        window.Echo.private('private-messenger.' + currentUserId)
            .listen('MessageSent', (e) => {
                const badge = document.getElementById('badge-' + e.message.sender_id);
                if (badge) {
                    let count = parseInt(badge.textContent || '0');
                    count++;
                    badge.textContent = count;
                    badge.style.display = 'inline-block';
                }
            })
            .listen('MessageRead', (e) => {
                const badge = document.getElementById('badge-' + e.sender_id);
                if (badge && e.reader_id === currentUserId) {
                    badge.textContent = '0';
                    badge.style.display = 'none';
                }
            });
    }

    // Update online/offline status from presence channel
    function updatePresence() {
        const onlineUsers = window.messengerOnlineUsers || {};
        @foreach($users as $user)
        const statusEl = document.getElementById('status-{{ $user->id }}');
        if (statusEl) {
            const userId = {{ $user->id }};
            if (onlineUsers[userId]) {
                statusEl.innerHTML = '<span class="online-dot online"></span>Online';
            } else {
                statusEl.innerHTML = '<span class="online-dot offline"></span>Offline';
            }
        }
        @endforeach
    }

    setInterval(updatePresence, 3000);
    setTimeout(updatePresence, 1000);
</script>

@endsection
