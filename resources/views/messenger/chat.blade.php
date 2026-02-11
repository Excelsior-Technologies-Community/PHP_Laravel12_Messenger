@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row" style="height:90vh;">

        <!-- Sidebar -->
        <div class="col-md-3 bg-dark text-white p-3">
            <h4>Laravel Messenger</h4>
            <hr>

            <a href="{{ route('messenger') }}" class="btn btn-light btn-sm">
                ← Back
            </a>
        </div>

        <!-- Chat Section -->
        <div class="col-md-9 d-flex flex-column p-0">

            <!-- Chat Header -->
            <div class="bg-primary text-white p-3">
                <h5 class="mb-0">{{ $receiver->name }}</h5>
            </div>

            <!-- Messages Area -->
            <div class="flex-grow-1 p-3 overflow-auto" style="background:#f1f1f1;">

                @forelse($messages as $msg)

                    @if($msg->sender_id == auth()->id())
                        <!-- Sender -->
                        <div class="d-flex justify-content-end mb-2">
                            <div class="bg-success text-white p-2 rounded" style="max-width:60%;">
                                {{ $msg->message }}
                                <div class="small text-end">
                                    {{ $msg->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Receiver -->
                        <div class="d-flex justify-content-start mb-2">
                            <div class="bg-white p-2 rounded border" style="max-width:60%;">
                                {{ $msg->message }}
                                <div class="small text-muted">
                                    {{ $msg->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif

                @empty
                    <p class="text-center text-muted">No messages yet</p>
                @endforelse

            </div>

            <!-- Message Input -->
            <div class="p-3 border-top bg-white">
                <form method="POST" action="{{ route('send.message') }}">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">

                    <div class="input-group">
                        <input type="text" name="message" class="form-control"
                               placeholder="Type message..." required>
                        <button class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
