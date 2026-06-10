@extends('layouts.dashboard')

@section('title', __('messages.appointments') . ' / ' . __('messages.queue'))
@section('page-title', __('messages.appointments') . ' / ' . __('messages.queue'))

@section('content')
<!-- Date & Filter -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center mb-3 mb-md-0">
        <div class="display-6 fw-bold text-primary me-3">{{ now()->format('D, M d') }}</div>
        <div class="text-muted h5 mb-0 fw-normal">{{ __('messages.appointmentsLabel') }}</div>
    </div>
    
    <form action="{{ route('appointments.queue') }}" method="GET" class="d-flex align-items-center">
        <label class="me-2 text-muted white-space-nowrap">{{ __('messages.filter') }} {{ __('messages.doctor') }}:</label>
        <select name="doctor_id" class="form-select" onchange="this.form.submit()">
            <option value="">{{ __('messages.allDoctors') }}</option>
            @foreach($doctors as $doctor)
                <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                    {{ $doctor->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<!-- Queue Columns -->
<div class="row g-4">
    <!-- 1. Upcoming (Not Arrived) -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0">
            <div class="card-header text-center py-3" style="background-color: rgba(15, 61, 62, 0.06); color: var(--secondary, #0f3d3e);">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar me-2"></i>{{ __('messages.pending') }}</h6>
                <span class="mt-1" style="display: inline-block; padding: 1px 10px; border-radius: var(--radius-sm, 6px); background-color: rgba(15, 61, 62, 0.1); color: var(--secondary, #0f3d3e); font-size: var(--text-sm, 0.875rem); font-weight: 600;">
                    {{ $appointments->whereIn('status', ['scheduled', 'pending'])->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->whereIn('status', ['scheduled', 'pending']) as $appt)
                    <div class="card mb-3 shadow-sm" style="border-left: 4px solid var(--secondary, #0f3d3e);">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">{{ $appt->patient->name }}</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="display: inline-block; padding: 1px 8px; border-radius: var(--radius-sm, 6px); font-size: var(--text-sm, 0.875rem); border: 1px solid var(--border-medium, rgba(26,26,26,0.12)); color: var(--text-primary, #2c2c2c);">{{ $appt->time->format('h:i A') }}</span>
                                <small style="color: var(--text-secondary, #555);">{{ ucfirst($appt->type) }}</small>
                            </div>
                            
                            <form action="{{ route('appointments.check-in', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm w-100" style="background: transparent; border: 1px solid var(--secondary, #0f3d3e); color: var(--secondary, #0f3d3e); border-radius: var(--radius-sm, 6px);">
                                    <i class="fas fa-check-square me-1"></i> {{ __('messages.checkIn') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 small" style="color: var(--text-secondary, #555);">{{ __('messages.noAppointments') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 2. With Nurse (Triage) -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0">
            <div class="card-header text-center py-3" style="background-color: rgba(61, 90, 128, 0.06); color: var(--info, #3d5a80);">
                <h6 class="mb-0 fw-bold"><i class="fas fa-user-nurse me-2"></i>{{ __('messages.checked_in') }}</h6>
                <span class="mt-1" style="display: inline-block; padding: 1px 10px; border-radius: var(--radius-sm, 6px); background-color: rgba(61, 90, 128, 0.1); color: var(--info, #3d5a80); font-size: var(--text-sm, 0.875rem); font-weight: 600;">
                    {{ $appointments->where('status', 'confirmed')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'confirmed') as $appt)
                    <div class="card mb-3 shadow-sm" style="border-left: 4px solid var(--info, #3d5a80);">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">{{ $appt->patient->name }}</h6>
                            <div class="small mb-2" style="color: var(--text-secondary, #555);">
                                {{ __('messages.arrived') }}: {{ $appt->checked_in_at ? $appt->checked_in_at->format('h:i A') : __('messages.now') }}
                            </div>
                            <div class="py-1 px-2 mb-0 small" style="background-color: rgba(61, 90, 128, 0.06); border-radius: var(--radius-sm, 6px); color: var(--info, #3d5a80);">
                                <i class="fas fa-spinner fa-spin me-1"></i> {{ __('messages.inTriage') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 small" style="color: var(--text-secondary, #555);">{{ __('messages.inTriage') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3. Ready for You (Priority) -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0">
            <div class="card-header text-center py-3" style="background-color: rgba(46, 93, 52, 0.06); color: var(--success, #2e5d34);">
                <h6 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2"></i>{{ __('messages.waiting') }}</h6>
                <span class="mt-1" style="display: inline-block; padding: 1px 10px; border-radius: var(--radius-sm, 6px); background-color: rgba(46, 93, 52, 0.1); color: var(--success, #2e5d34); font-size: var(--text-sm, 0.875rem); font-weight: 600;">
                    {{ $appointments->where('status', 'waiting')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'waiting') as $appt)
                    <div class="card mb-3 shadow-sm" style="border-left: 4px solid var(--success, #2e5d34);">
                        <div class="card-header py-2" style="background-color: rgba(46, 93, 52, 0.06); border-bottom: none;">
                             <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold" style="color: var(--success, #2e5d34);">{{ $appt->patient->name }}</span>
                                <span style="display: inline-block; padding: 1px 8px; border-radius: var(--radius-sm, 6px); background-color: var(--success, #2e5d34); color: #fff; font-size: var(--text-xs, 0.75rem); font-weight: 600;">{{ __('messages.priority') }}</span>
                             </div>
                        </div>
                        <div class="card-body p-3">
                            <!-- Vitals Display -->
                            @if($appt->vital)
                            <div class="mb-3 p-2 text-center" style="border-radius: var(--radius-sm, 6px); background-color: rgba(15, 61, 62, 0.03);">
                                <div class="row g-0">
                                    <div class="col-6" style="border-right: 1px solid var(--border-hairline, rgba(26,26,26,0.04));">
                                        <small class="d-block" style="color: var(--text-secondary, #555);">{{ __('messages.bp') }}</small>
                                        <span class="fw-bold" style="color: var(--text-primary, #2c2c2c);">{{ $appt->vital->blood_pressure }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="d-block" style="color: var(--text-secondary, #555);">{{ __('messages.temp') }}</small>
                                        <span class="fw-bold {{ $appt->vital->temperature > 37.5 ? '' : '' }}" style="color: {{ $appt->vital->temperature > 37.5 ? 'var(--danger, #8b3a3a)' : 'var(--text-primary, #2c2c2c)' }};">{{ $appt->vital->temperature }}°C</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="small mb-2" style="color: var(--danger, #8b3a3a);">
                                <i class="fas fa-clock me-1"></i> 
                                {{ __('messages.waiting') }}: <span class="waiting-timer" data-time="{{ $appt->checked_in_at }}">0m</span>
                            </div>
                            
                            <form action="{{ route('appointments.start', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn w-100 fw-bold" style="background-color: var(--success, #2e5d34); color: #fff; border: none; border-radius: var(--radius-sm, 6px); padding: 8px 16px; box-shadow: var(--shadow-subtle, 0 1px 2px rgba(26,26,26,0.04));">
                                    {{ __('messages.startVisit') }} <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 small" style="color: var(--text-secondary, #555);">{{ __('messages.waitingRoomEmpty') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. In Progress -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0">
            <div class="card-header text-center py-3" style="background-color: rgba(26, 26, 46, 0.06); color: var(--primary, #1a1a2e);">
                <h6 class="mb-0 fw-bold"><i class="fas fa-stethoscope me-2"></i>{{ __('messages.in_progress') }}</h6>
                <span class="mt-1" style="display: inline-block; padding: 1px 10px; border-radius: var(--radius-sm, 6px); background-color: rgba(26, 26, 46, 0.1); color: var(--primary, #1a1a2e); font-size: var(--text-sm, 0.875rem); font-weight: 600;">
                    {{ $appointments->where('status', 'in_progress')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'in_progress') as $appt)
                    <div class="card mb-3 shadow-sm" style="border-left: 4px solid var(--primary, #1a1a2e);">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">{{ $appt->patient->name }}</h6>
                            <div class="mb-2">
                                <span class="animate-pulse" style="display: inline-block; padding: 2px 10px; border-radius: var(--radius-sm, 6px); background-color: rgba(26, 26, 46, 0.06); color: var(--primary, #1a1a2e); font-size: var(--text-sm, 0.875rem); font-weight: 600;">
                                    <i class="fas fa-circle fa-xs me-1"></i> {{ __('messages.liveSession') }}
                                </span>
                            </div>
                            <div class="small mb-3" style="color: var(--text-secondary, #555);">
                                <i class="fas fa-stopwatch me-1"></i> 
                                <span class="waiting-timer" data-time="{{ $appt->started_at }}">0m</span>
                            </div>
                            
                            <form action="{{ route('appointments.complete', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn w-100" style="background: transparent; border: 1px solid var(--primary, #1a1a2e); color: var(--primary, #1a1a2e); border-radius: var(--radius-sm, 6px);">
                                    {{ __('messages.completeVisit') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 small" style="color: var(--text-secondary, #555);">{{ __('messages.noActiveSessions') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Live update for waiting times
    function updateTimers() {
        document.querySelectorAll('.waiting-timer').forEach(el => {
            const startTime = new Date(el.dataset.time);
            if (isNaN(startTime.getTime())) return;
            
            const diff = Math.floor((new Date() - startTime) / 60000); // minutes
            el.textContent = diff + 'm';
            
            // Highlight long waits
            if (diff > 30) {
                el.classList.add('fw-bold');
            }
        });
    }

    setInterval(updateTimers, 60000); // Update every minute
    updateTimers(); // Initial call
</script>
<style>
    .queue-column {
        min-height: 400px;
        height: calc(100vh - 180px);
        overflow-y: auto;
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
</style>
@endsection
