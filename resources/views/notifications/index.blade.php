@extends('layouts.dashboard')

@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Notifications</h4>
    <div class="d-flex gap-2">
        @if($notifications->count() > 0)
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary shadow-sm">
                <i class="fas fa-check-double me-2"></i> Mark all read
            </button>
        </form>
        <form action="{{ route('notifications.clear-all') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all notifications?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger shadow-sm">
                <i class="fas fa-trash me-2"></i> Clear All
            </button>
        </form>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($notifications->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <div class="list-group-item p-4 d-flex align-items-start {{ is_null($notification->read_at) ? ' ' : '' }}">
                        <div class="flex-shrink-0 me-3">
                            @php
                                $icon = 'fa-info-circle text-primary';
                                $bg = 'bg-primary-subtle';
                                if($notification->type == 'appointment') { $icon = 'fa-calendar-check text-success'; $bg = 'bg-success-subtle'; }
                                if($notification->type == 'payment') { $icon = 'fa-file-invoice-dollar text-warning'; $bg = 'bg-warning-subtle'; }
                                if($notification->type == 'system') { $icon = 'fa-cogs text-secondary'; $bg = 'bg-secondary-subtle'; }
                            @endphp
                            <div class="avatar {{ $bg }} rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas {{ $icon }} fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold">{{ $notification->title }}</h6>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-2 text-muted">{{ $notification->message }}</p>
                            
                            <div class="d-flex gap-3 align-items-center">
                                @if($notification->link)
                                    <a href="{{ $notification->link }}" class="text-decoration-none small fw-bold text-primary">
                                        View Details <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                @endif

                                @if(is_null($notification->read_at))
                                    <form action="{{ route('notifications.mark-as-read', $notification) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0 text-muted small">Mark as read</button>
                                    </form>
                                @else
                                    <span class="text-success small"><i class="fas fa-check me-1"></i> Read</span>
                                @endif

                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="d-inline ms-auto" onsubmit="return confirm('Delete this notification?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0 text-danger" title="Delete">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center p-5">
                <div class="mb-3">
                    <i class="far fa-bell-slash fa-4x text-muted opacity-50"></i>
                </div>
                <h5 class="text-muted">No notifications found</h5>
                <p class="text-muted small">You're all caught up!</p>
            </div>
        @endif
    </div>
    @if($notifications->hasPages())
        <div class="card-footer   py-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
