@extends('layouts.app')

@section('content')

<style>
    .messenger-wrapper {
        height: 92vh;
        background: #f4f6fb;
    }

    /* Sidebar */
    .user-sidebar {
        background: #111827;
        color: white;
        overflow-y: auto;
    }

    /* User card */
    .user-item {
        background: rgba(255, 255, 255, 0.06);
        padding: 12px;
        border-radius: 12px;
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .user-item:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateX(3px);
    }

    /* Avatar */
    .avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        margin-right: 10px;
    }

    /* User name */
    .user-name {
        font-weight: 500;
    }

    /* Badge */
    .badge-custom {
        background: #ef4444;
        font-size: 12px;
        padding: 5px 8px;
        border-radius: 20px;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        color: #6b7280;
    }
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
                class="text-decoration-none text-white">

                <div class="user-item">

                    <div class="d-flex align-items-center">

                        <div class="avatar">
                            {{ strtoupper(substr($user->name,0,1)) }}
                        </div>

                        <div class="user-name">
                            {{ $user->name }}
                        </div>

                    </div>

                    @if($user->unread_count > 0)
                    <span class="badge-custom">
                        {{ $user->unread_count }}
                    </span>
                    @endif

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
                    width="90"
                    class="mb-3">

                <h4 class="text-muted">
                    Select a conversation
                </h4>

                <p class="text-secondary">
                    Choose a user from the left sidebar to start chatting.
                </p>

            </div>

        </div>

    </div>

</div>

@endsection