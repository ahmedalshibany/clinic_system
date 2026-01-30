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
    <!-- 1. Upcoming (Not Arrived) -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 bg-light border-0">
            <div class="card-header bg-secondary text-white text-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar me-2"></i>Upcoming</h6>
                <span class="badge bg-white text-secondary rounded-pill mt-1">
                    {{ $appointments->whereIn('status', ['scheduled', 'pending'])->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->whereIn('status', ['scheduled', 'pending']) as $appt)
                    <div class="card mb-3 shadow-sm border-start border-4 border-secondary">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">{{ $appt->patient->name }}</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-dark fw-normal border">{{ $appt->time->format('h:i A') }}</span>
                                <small class="text-muted">{{ ucfirst($appt->type) }}</small>
                            </div>
                            
                            <form action="{{ route('appointments.check-in', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fas fa-check-square me-1"></i> Check In
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 small">No upcoming visits</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 2. With Nurse (Triage) -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 bg-light border-0">
            <div class="card-header bg-info text-white text-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-user-nurse me-2"></i>With Nurse</h6>
                <span class="badge bg-white text-info rounded-pill mt-1">
                    {{ $appointments->where('status', 'confirmed')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'confirmed') as $appt)
                    <div class="card mb-3 shadow-sm border-start border-4 border-info">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">{{ $appt->patient->name }}</h6>
                            <div class="text-muted small mb-2">
                                Arrived: {{ $appt->checked_in_at ? $appt->checked_in_at->format('h:i A') : 'Now' }}
                            </div>
                            <div class="alert alert-info py-1 px-2 mb-0 small">
                                <i class="fas fa-spinner fa-spin me-1"></i> In Triage
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 small">No patients in triage</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3. Ready for You (Priority) -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 bg-light border-0">
            <div class="card-header bg-success text-white text-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2"></i>Ready for You</h6>
                <span class="badge bg-white text-success rounded-pill mt-1">
                    {{ $appointments->where('status', 'waiting')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'waiting') as $appt)
                    <div class="card mb-3 shadow border-start border-4 border-success">
                        <div class="card-header bg-success bg-opacity-10 py-2">
                             <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-success">{{ $appt->patient->name }}</span>
                                <span class="badge bg-success">Priority</span>
                             </div>
                        </div>
                        <div class="card-body p-3">
                            <!-- Vitals Display -->
                            @if($appt->vital)
                            <div class="mb-3 p-2 bg-light rounded text-center">
                                <div class="row g-0">
                                    <div class="col-6 border-end">
                                        <small class="text-muted d-block">BP</small>
                                        <span class="fw-bold text-dark">{{ $appt->vital->blood_pressure }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Temp</small>
                                        <span class="fw-bold {{ $appt->vital->temperature > 37.5 ? 'text-danger' : 'text-dark' }}">{{ $appt->vital->temperature }}°C</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="text-danger small mb-2">
                                <i class="fas fa-clock me-1"></i> 
                                Waiting: <span class="waiting-timer" data-time="{{ $appt->checked_in_at }}">0m</span>
                            </div>
                            
                            <form action="{{ route('appointments.start', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">
                                    Start Visit <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 small">Waiting room empty</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. In Progress -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 bg-light border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-stethoscope me-2"></i>In Progress</h6>
                <span class="badge bg-white text-primary rounded-pill mt-1">
                    {{ $appointments->where('status', 'in_progress')->count() }}
                </span>
            </div>
            <div class="card-body p-3 queue-column">
                @forelse($appointments->where('status', 'in_progress') as $appt)
                    <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">{{ $appt->patient->name }}</h6>
                            <div class="mb-2">
                                <span class="badge bg-primary-soft text-primary animate-pulse">
                                    <i class="fas fa-circle fa-xs me-1"></i> Live Session
                                </span>
                            </div>
                            <div class="small text-muted mb-3">
                                <i class="fas fa-stopwatch me-1"></i> 
                                <span class="waiting-timer" data-time="{{ $appt->started_at }}">0m</span>
                            </div>
                            
                            <form action="{{ route('appointments.complete', $appt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    Finish & Complete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 small">No active sessions</div>
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
