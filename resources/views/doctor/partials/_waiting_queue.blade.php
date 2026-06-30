@if($triageQueue->isNotEmpty())
<div class="mb-3">
    <h6 class="small fw-bold text-uppercase text-muted mb-2 px-2">
        <i class="fas fa-triage me-1"></i>{{ __('messages.doctor_triage_queue') }}
        <span class="badge bg-info ms-1">{{ $triageQueue->count() }}</span>
    </h6>
    @foreach($triageQueue as $appt)
    <div class="card mb-2 border-0" style="border-left: 4px solid {{ $appt->status === 'paid' ? 'var(--secondary)' : 'var(--info)' }} !important; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="fw-bold mb-0" style="color: var(--primary);">
                        <a href="{{ route('patients.show', $appt->patient_id) }}" class="text-decoration-none" style="color: var(--primary);" target="_blank">
                            {{ $appt->patient->name }}
                            <i class="fas fa-external-link-alt" style="font-size: 0.6rem;"></i>
                        </a>
                    </h6>
                    <small class="text-muted">{{ $appt->patient->patient_code ?? '' }} · {{ $appt->time->format('H:i') }}</small>
                </div>
                <span class="small text-muted">
                    <i class="fas fa-clock me-1"></i>
                    @if($appt->checked_in_at)
                    <span class="waiting-timer" data-time="{{ $appt->checked_in_at?->toIso8601String() }}">0{{ __('messages.doctor_minutes') }}</span>
                    @else
                    <span>{{ $appt->time->format('H:i') }}</span>
                    @endif
                </span>
            </div>

            @if($appt->status === 'paid')
            <div class="p-2 mb-2 rounded text-center small" style="background-color: rgba(var(--secondary-rgb, 108, 117, 125), 0.08);">
                <span class="text-muted"><i class="fas fa-user-clock me-1"></i>{{ __('messages.awaiting_check_in') }}</span>
            </div>
            @else
            <div class="p-2 mb-2 rounded text-center small" style="background-color: rgba(var(--info-rgb, 13, 202, 240), 0.08);">
                <span class="text-muted"><i class="fas fa-clock me-1"></i>{{ __('messages.doctor_awaiting_triage') }}</span>
            </div>
            @endif

            @if($appt->status === 'checked_in')
            <div class="d-flex gap-2">
                <form action="{{ route('doctor.appointments.request-vitals', $appt) }}" method="POST" class="flex-grow-1">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100" style="font-size: 0.75rem;">
                        <i class="fas fa-heartbeat me-1"></i>{{ __('messages.doctor_needs_vitals') }}
                    </button>
                </form>
                <form action="{{ route('doctor.appointments.direct-to-room', $appt) }}" method="POST" class="flex-grow-1">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm w-100" style="font-size: 0.75rem;">
                        <i class="fas fa-door-open me-1"></i>{{ __('messages.doctor_direct_room') }}
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

@if($readyQueue->isNotEmpty())
<div class="mb-3">
    <h6 class="small fw-bold text-uppercase text-muted mb-2 px-2">
        <i class="fas fa-user-check me-1"></i>{{ __('messages.doctor_ready_queue') }}
        <span class="badge bg-warning ms-1">{{ $readyQueue->count() }}</span>
    </h6>
    @foreach($readyQueue as $appt)
    <div class="card mb-2 border-0" style="border-left: 4px solid var(--warning) !important; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="fw-bold mb-0" style="color: var(--primary);">
                        <a href="{{ route('patients.show', $appt->patient_id) }}" class="text-decoration-none" style="color: var(--primary);" target="_blank">
                            {{ $appt->patient->name }}
                            <i class="fas fa-external-link-alt" style="font-size: 0.6rem;"></i>
                        </a>
                    </h6>
                    <small class="text-muted">{{ $appt->patient->patient_code ?? '' }} · {{ $appt->time->format('H:i') }}</small>
                </div>
                <span class="small text-muted">
                    <i class="fas fa-clock me-1"></i>
                    <span class="waiting-timer" data-time="{{ $appt->checked_in_at?->toIso8601String() }}">0{{ __('messages.doctor_minutes') }}</span>
                </span>
            </div>

            @if($appt->vital)
            <div class="p-2 mb-2 rounded" style="background-color: var(--cream);">
                <div class="row g-1 text-center small">
                    <div class="col-4">
                        <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_bp') }}</span>
                        <span class="fw-semibold" style="color: var(--primary);">{{ $appt->vital->blood_pressure }}</span>
                    </div>
                    <div class="col-4">
                        <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_temp') }}</span>
                        <span class="fw-semibold" style="color: {{ $appt->vital->temperature > 37.5 ? 'var(--danger)' : 'var(--primary)' }};">{{ $appt->vital->temperature }}°C</span>
                    </div>
                    <div class="col-4">
                        <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_bmi') }}</span>
                        <span class="fw-semibold" style="color: var(--primary);">
                            {{ $appt->vital->bmi ?? '--' }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('doctor.appointments.start', $appt) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-davinci-primary w-100" style="font-size: 0.85rem;">
                    <i class="fas fa-play me-2"></i>{{ __('messages.doctor_start_exam') }}
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($triageQueue->isEmpty() && $readyQueue->isEmpty())
<div class="text-center py-5">
    <i class="fas fa-bed text-muted mb-3" style="font-size: 2rem; opacity: 0.4;"></i>
    <p class="text-muted small mb-0">{{ __('messages.doctor_waiting_room_empty') }}</p>
</div>
@endif