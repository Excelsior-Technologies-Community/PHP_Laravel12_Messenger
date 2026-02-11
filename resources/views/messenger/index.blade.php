@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row" style="height:90vh;">

        <!-- Sidebar -->
        <div class="col-md-4 col-lg-3 bg-dark text-white p-3">
            <h4>Laravel Messenger</h4>
            <hr>

            @foreach($users as $user)
                <div class="mb-2">
                    <a href="{{ route('chat',$user->id) }}" 
                       class="text-white text-decoration-none">
                        <div class="p-2 rounded bg-secondary">
                            {{ $user->name }}
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Right Side -->
        <div class="col-md-8 col-lg-9 d-flex align-items-center justify-content-center">
            <h4 class="text-muted">Select a user to start chatting</h4>
        </div>

    </div>
</div>
@endsection
