@forelse($notifications as $notification)
    @php
        $_data = $notification->data ?? [];
        $_title = isset($_data['title_key']) ? __("messages.{$_data['title_key']}", $_data) : $notification->title;
        $_message = isset($_data['message_key']) ? __("messages.{$_data['message_key']}", $_data) : $notification->message;
    @endphp
    <a href="{{ route('notifications.index') }}" class="dropdown-item p-3 border-bottom {{ is_null($notification->read_at) ? ' ' : '' }}">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                @php
                    $icon = 'fa-info-circle text-primary';
                    if($notification->type == 'appointment') $icon = 'fa-calendar-check text-success';
                    if($notification->type == 'payment') $icon = 'fa-file-invoice-dollar text-warning';
                    if($notification->type == 'system') $icon = 'fa-cogs text-secondary';
                @endphp
                <div class="avatar avatar-sm   rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                    <i class="fas {{ $icon }}"></i>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="mb-1 small fw-bold text-dark">{{ $_title }}</h6>
                <p class="mb-1 small text-muted text-truncate" style="max-width: 200px;">{{ $_message }}</p>
                <small class="text-xs text-muted">
                    <i class="far fa-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                </small>
            </div>
            @if(is_null($notification->read_at))
                <div class="flex-shrink-0 ms-2">
                    <span class="badge bg-primary rounded-circle p-1" style="width: 8px; height: 8px;"></span>
                </div>
            @endif
        </div>
    </a>
@empty
    <div class="text-center p-4">
        <div class="mb-2">
            <i class="far fa-bell-slash fa-2x text-muted"></i>
        </div>
        <p class="text-muted small mb-0">{{ __('messages.noNotifications') }}</p>
    </div>
@endforelse

@if($notifications->count() > 0)
    <div class="dropdown-footer text-center p-2  ">
        <a href="{{ route('notifications.index') }}" class="small text-decoration-none fw-bold">{{ __('messages.viewAllNotifications') }}</a>
    </div>
@endif
