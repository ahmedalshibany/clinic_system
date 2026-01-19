@extends('layouts.dashboard')

@section('title', 'Queue Management')
@section('page-title', 'Queue Management')

@section('content')
<!-- Date & Filter -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center mb-3 mb-md-0">
        <div class="display-6 fw-bold text-primary me-3">{{ now()->format('D, M d') }}</div>
        <div class="text-muted h5 mb-0 fw-normal">Today's Queue</div>
    </div>
    
    <form action="{{ route('appointments.queue') }}" method="GET" class="d-flex align-items-center">
        <label class="me-2 text-muted white-space-nowrap">Filter by Doctor:</label>
        <select name="doctor_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Doctors</option>
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
    <!-- 1. Scheduled / Upcoming -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 bg-light border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Scheduled</h5>
                <span class="badge bg-white text-primary rounded-pill mt-1">
                    {{ $appointments->where('status', 'scheduled')->count() + $appointments->where('status', 'confirmed')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->whereIn('status', ['scheduled', 'confirmed', 'pending']) as $appt)
                    <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">{{ $appt->patient->name }}</h6>
                                <span class="badge bg-light text-dark fw-normal">{{ $appt->time->format('h:i A') }}</span>
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-user-md me-1"></i> {{ $appt->doctor->name }}
                            </div>
                            
                            <form action="{{ route('appointments.check-in', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-check-square me-1"></i> Check In
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <p class="small mb-0">No upcoming appointments</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 2. Waiting Room -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 bg-light border-0">
            <div class="card-header bg-warning text-dark text-center py-3">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Waiting</h5>
                <span class="badge bg-dark text-warning rounded-pill mt-1">
                    {{ $appointments->where('status', 'waiting')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'waiting') as $appt)
                    <div class="card mb-3 shadow-sm border-start border-4 border-warning">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">{{ $appt->patient->name }}</h6>
                                <span class="text-muted small">
                                    Arrived: {{ $appt->checked_in_at ? $appt->checked_in_at->format('h:i A') : '-' }}
                                </span>
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-user-md me-1"></i> {{ $appt->doctor->name }}
                            </div>
                            <div class="text-danger small mb-2">
                                <i class="fas fa-stopwatch me-1"></i> 
                                <span class="waiting-timer" data-time="{{ $appt->checked_in_at }}">0m</span>
                            </div>
                            
                            <form action="{{ route('appointments.start', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning w-100">
                                    <i class="fas fa-play me-1"></i> Start Visit
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <p class="small mb-0">Waiting room empty</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3. In Progress -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 bg-light border-0">
            <div class="card-header bg-success text-white text-center py-3">
                <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>In Progress</h5>
                <span class="badge bg-white text-success rounded-pill mt-1">
                    {{ $appointments->where('status', 'in_progress')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'in_progress') as $appt)
                    <div class="card mb-3 shadow-sm border-start border-4 border-success">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">{{ $appt->patient->name }}</h6>
                                <span class="badge bg-success-soft text-success animate-pulse">Live</span>
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-user-md me-1"></i> {{ $appt->doctor->name }}
                            </div>
                            <div class="small text-muted mb-2">
                                Started: {{ $appt->started_at ? $appt->started_at->format('h:i A') : '-' }}
                            </div>
                            
                            <form action="{{ route('appointments.complete', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success w-100">
                                    <i class="fas fa-check me-1"></i> Complete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <p class="small mb-0">No active visits</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. Completed -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 bg-light border-0">
            <div class="card-header bg-secondary text-white text-center py-3">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Completed</h5>
                <span class="badge bg-white text-dark rounded-pill mt-1">
                    {{ $appointments->where('status', 'completed')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'completed') as $appt)
                    <div class="card mb-3 shadow-sm border-start border-4 border-secondary opacity-75">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0">{{ $appt->patient->name }}</h6>
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="small text-muted mb-1">
                                <i class="fas fa-user-md me-1"></i> {{ $appt->doctor->name }}
                            </div>
                            <div class="small text-muted">
                                Finished: {{ $appt->completed_at ? $appt->completed_at->format('h:i A') : '-' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <p class="small mb-0">No completed visits</p>
                    </div>
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
        max-height: 70vh;
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
