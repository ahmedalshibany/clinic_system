@extends('layouts.dashboard')

@section('title', __('messages.dashboard') . ' / ' . __('messages.doctor_clinical_board'))
@section('page-title', __('messages.doctor_clinical_board'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="display-6 fw-bold" style="color: #1a1a2e;">{{ now()->translatedFormat('l, j F') }}</div>
        <div class="text-muted small">{{ __('messages.doctor_prefix') }} {{ $doctor->name }} · {{ __('messages.' . $doctor->specialty) }}</div>
    </div>
    <div class="d-flex gap-3 small">
        <span><strong>{{ $stats['total'] }}</strong> {{ __('messages.doctor_total_today') }}</span>
        <span style="color: #f59e0b;"><strong>{{ $stats['waiting'] }}</strong> {{ __('messages.doctor_waiting_ready') }}</span>
        <span style="color: #1a1a2e;"><strong>{{ $stats['in_progress'] }}</strong> {{ __('messages.doctor_in_progress_exam') }}</span>
        <span style="color: #10b981;"><strong>{{ $stats['completed'] }}</strong> {{ __('messages.doctor_completed_done') }}</span>
    </div>
</div>

<div class="row g-4">
    {{-- LEFT: Live Consultation Queue --}}
    <div class="col-lg-5">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: #f5f0e8; border-bottom: 2px solid #1a1a2e;">
                <h6 class="mb-0 fw-bold" style="color: #1a1a2e;">
                    <i class="fas fa-chair me-2"></i>{{ __('messages.doctor_waiting_room_ready') }}
                </h6>
                <span class="badge rounded-pill px-3" style="background-color: #f59e0b; color: #fff;">{{ $waitingQueue->count() }}</span>
            </div>
            <div class="card-body p-3" style="min-height: 400px; max-height: calc(100vh - 220px); overflow-y: auto; background-color: #f5f0e8;">
                @forelse($waitingQueue as $appt)
                    <div class="card mb-3 border-0" style="border-left: 4px solid #1a1a2e !important; background-color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold mb-0" style="color: #1a1a2e;">{{ $appt->patient->name }}</h6>
                                    <small class="text-muted">{{ $appt->patient->patient_code ?? '' }} · {{ $appt->time->format('H:i') }}</small>
                                </div>
                                <span class="small text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <span class="waiting-timer" data-time="{{ $appt->checked_in_at }}">0{{ __('messages.doctor_minutes') }}</span>
                                </span>
                            </div>

                            @if($appt->vital)
                            <div class="p-2 mb-2 rounded" style="background-color: rgba(26, 26, 46, 0.03);">
                                <div class="row g-1 text-center small">
                                    <div class="col-4">
                                        <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_bp') }}</span>
                                        <span class="fw-semibold" style="color: #1a1a2e;">{{ $appt->vital->blood_pressure }}</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_temp') }}</span>
                                        <span class="fw-semibold" style="color: {{ $appt->vital->temperature > 37.5 ? '#8b3a3a' : '#1a1a2e' }};">{{ $appt->vital->temperature }}°C</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_bmi') }}</span>
                                        <span class="fw-semibold" style="color: #1a1a2e;">
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
                                <button type="submit" class="btn w-100 fw-bold" style="background-color: #1a1a2e; color: #fff; border: none; border-radius: 6px; padding: 8px 16px; font-size: 0.85rem;">
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
            </div>
        </div>
    </div>

    {{-- RIGHT: E-Prescription / Diagnosis Board --}}
    <div class="col-lg-7">
        @if($activeSession)
            {{-- Active Session Card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="d-inline-block px-3 py-1 rounded-pill mb-2" style="background-color: rgba(16, 185, 129, 0.12); color: #10b981; font-size: 0.75rem; font-weight: 700;">
                                <span class="live-pulse-dot me-1" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span>
                                {{ __('messages.doctor_live_session') }}
                            </span>
                            <h5 class="fw-bold mb-1" style="color: #1a1a2e;">{{ $activeSession->patient->name }}</h5>
                            <small class="text-muted">{{ $activeSession->patient->patient_code ?? '' }} · {{ $activeSession->time->format('H:i') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="d-block fw-bold" style="color: #1a1a2e; font-size: 1.2rem;" id="sessionElapsed">00:00</span>
                            <small class="text-muted">{{ __('messages.doctor_elapsed') }}</small>
                        </div>
                    </div>

                    @if($activeSession->vital)
                    <div class="p-3 rounded mb-3" style="background-color: rgba(26, 26, 46, 0.03);">
                        <div class="row g-2 text-center">
                            <div class="col-3">
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_bp') }}</span>
                                <span class="fw-semibold" style="color: #1a1a2e;">{{ $activeSession->vital->blood_pressure }}</span>
                            </div>
                            <div class="col-3">
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_temp') }}</span>
                                <span class="fw-semibold" style="color: #1a1a2e;">{{ $activeSession->vital->temperature }}°C</span>
                            </div>
                            <div class="col-3">
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_bmi') }}</span>
                                <span class="fw-semibold" style="color: #1a1a2e;">
                                    {{ $activeSession->vital->bmi ?? '--' }}
                                    @if($activeSession->vital->bmi_category)
                                        <span class="text-muted fw-normal">({{ ['Underweight' => __('messages.bmi_underweight'), 'Normal' => __('messages.bmi_normal'), 'Overweight' => __('messages.bmi_overweight'), 'Obese' => __('messages.bmi_obese')][$activeSession->vital->bmi_category] ?? $activeSession->vital->bmi_category }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="col-3">
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_pulse') }}</span>
                                <span class="fw-semibold" style="color: #1a1a2e;">{{ $activeSession->vital->pulse ?? '--' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Diagnosis & Notes Form --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3 px-4" style="background-color: #f5f0e8; border-bottom: 2px solid #1a1a2e;">
                    <h6 class="mb-0 fw-bold" style="color: #1a1a2e;">
                        <i class="fas fa-prescription me-2"></i>{{ __('messages.doctor_diagnosis_notes') }}
                    </h6>
                </div>
                <div class="card-body p-4" style="background-color: #fff;">
                    <form action="{{ route('doctor.appointments.complete', $activeSession) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold small" style="color: #1a1a2e;">{{ __('messages.doctor_diagnosis_label') }}</label>
                            <textarea name="diagnosis" rows="4" class="form-control" placeholder="{{ __('messages.doctor_diagnosis_placeholder') }}" style="border: 1px solid rgba(26,26,46,0.15); border-radius: 6px; resize: vertical; background-color: #fff; color: #1a1a2e;">{{ old('diagnosis', $activeSession->diagnosis) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small" style="color: #1a1a2e;">{{ __('messages.doctor_notes_label') }}</label>
                            <textarea name="notes" rows="6" class="form-control" placeholder="{{ __('messages.doctor_notes_placeholder') }}" style="border: 1px solid rgba(26,26,46,0.15); border-radius: 6px; resize: vertical; background-color: #fff; color: #1a1a2e;">{{ old('notes', $activeSession->notes) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn fw-bold px-4" style="background-color: #1a1a2e; color: #fff; border: none; border-radius: 6px; padding: 10px 24px;">
                                <i class="fas fa-check-double me-2"></i>{{ __('messages.doctor_complete_exam') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($waitingQueue->isNotEmpty())
            {{-- No active session -- select from queue --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <i class="fas fa-hand-pointer text-muted mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    <h5 class="text-muted fw-normal">{{ __('messages.doctor_select_patient') }}</h5>
                    <p class="small text-muted">{{ __('messages.doctor_click_start') }}</p>
                </div>
            </div>
        @else
            {{-- Empty state --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <i class="fas fa-check-circle mb-3" style="font-size: 2.5rem; color: #10b981; opacity: 0.5;"></i>
                    <h5 class="fw-normal" style="color: #1a1a2e;">{{ __('messages.doctor_all_clear') }}</h5>
                    <p class="small text-muted">{{ __('messages.doctor_no_patients') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Elapsed timer for active session
    @if($activeSession && $activeSession->started_at)
    (function() {
        const startedAt = new Date("{{ $activeSession->started_at }}").getTime();
        const el = document.getElementById('sessionElapsed');

        function updateElapsed() {
            const diff = Math.floor((Date.now() - startedAt) / 1000);
            const m = Math.floor(diff / 60).toString().padStart(2, '0');
            const s = (diff % 60).toString().padStart(2, '0');
            if (el) el.textContent = m + ':' + s;
        }

        updateElapsed();
        setInterval(updateElapsed, 1000);
    })();
    @endif

    // Waiting timers for queue cards
    (function() {
        function updateTimers() {
            document.querySelectorAll('.waiting-timer').forEach(el => {
                const startTime = new Date(el.dataset.time).getTime();
                if (isNaN(startTime)) return;
                const diff = Math.floor((Date.now() - startTime) / 60000);
                el.textContent = diff + '{{ __('messages.doctor_minutes') }}';
                if (diff > 30) el.style.fontWeight = 'bold';
            });
        }
        updateTimers();
        setInterval(updateTimers, 60000);
    })();
</script>
<style>
    .live-pulse-dot {
        animation: live-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes live-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.85); }
    }
</style>
@endsection
