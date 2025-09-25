@extends('layouts.frontend')
@section('content')
    <div class="container">
        <h3 class="mb-4">My Notifications</h3>

        <ul class="list-group">
            @forelse(auth()->user()->notifications as $notification)
                <li
                    class="list-group-item d-flex justify-content-between align-items-start {{ $notification->read_at ? '' : 'list-group-item-info' }}">
                    <div>
                        <strong>{{ $notification->data['message'] ?? 'Notification' }}</strong>
                        <br>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-sm btn-outline-success">Mark as Read</button>
                    </form>
                </li>
            @empty
                <li class="list-group-item text-muted">You have no notifications.</li>
            @endforelse
        </ul>
    </div>
@endsection