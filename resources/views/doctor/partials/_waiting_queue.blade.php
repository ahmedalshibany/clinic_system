@forelse($waitingQueue as $appt)
    <div class="card mb-3 border-0" style="border-left: 4px solid var(--primary) !important; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="fw-bold mb-0" style="color: var(--primary);">{{ $appt->patient->name }}</h6>
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
                            @if($appt->vital->bmi_category)
                                <span class="text-muted fw-normal">({{ ['Underweight' => __('messages.bmi_underweight'), 'Normal' => __('messages.bmi_normal'), 'Overweight' => __('messages.bmi_overweight'), 'Obese' => __('messages.bmi_obese')][$appt->vital->bmi_category] ?? $appt->vital->bmi_category }})</span>
                            @endif
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
@empty
    <div class="text-center py-5">
        <i class="fas fa-bed text-muted mb-3" style="font-size: 2rem; opacity: 0.4;"></i>
        <p class="text-muted small mb-0">{{ __('messages.doctor_waiting_room_empty') }}</p>
    </div>
@endforelse
