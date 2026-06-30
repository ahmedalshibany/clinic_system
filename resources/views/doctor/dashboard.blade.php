@extends('layouts.dashboard')

@section('title', __('messages.dashboard') . ' / ' . __('messages.doctor_clinical_board'))
@section('page-title', __('messages.doctor_clinical_board'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="display-6 fw-bold" style="color: var(--primary);">{{ now()->translatedFormat('l, j F') }}</div>
        <div class="text-muted small">{{ __('messages.doctor_prefix') }} {{ $doctor->name }} · {{ __('messages.' . $doctor->specialty) }}</div>
    </div>
    <div class="d-flex gap-3 small">
        <span><strong>{{ $stats['total'] }}</strong> {{ __('messages.doctor_total_today') }}</span>
        <span style="color: var(--info);"><strong>{{ $stats['triage'] }}</strong> {{ __('messages.doctor_in_triage') }}</span>
        <span style="color: var(--warning);"><strong>{{ $stats['waiting'] }}</strong> {{ __('messages.doctor_waiting_ready') }}</span>
        <span style="color: var(--primary);"><strong>{{ $stats['in_progress'] }}</strong> {{ __('messages.doctor_in_progress_exam') }}</span>
        <span style="color: var(--success);"><strong>{{ $stats['completed'] }}</strong> {{ __('messages.doctor_completed_done') }}</span>
    </div>
</div>

<div class="row g-4">
    {{-- LEFT: Triage Queue + Ready Queue --}}
    <div class="col-lg-5">
        <div class="card h-100 border-0 shadow-sm clinical-board-waiting" id="waitingQueuePanel">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: var(--cream); border-bottom: 2px solid var(--primary);">
                <h6 class="mb-0 fw-bold" style="color: var(--primary);">
                    <i class="fas fa-chair me-2"></i>{{ __('messages.doctor_waiting_room_ready') }}
                </h6>
                <span class="badge rounded-pill px-3" style="background-color: var(--warning); color: #fff;">{{ $triageQueue->count() + $readyQueue->count() }}</span>
            </div>
            <div class="card-body p-3" style="min-height: 400px; max-height: calc(100vh - 220px); overflow-y: auto; background-color: var(--panel-bg);">
                @include('doctor.partials._waiting_queue', ['triageQueue' => $triageQueue, 'readyQueue' => $readyQueue])
            </div>
        </div>
    </div>

    {{-- RIGHT: Active Session / Diagnosis Board --}}
    <div class="col-lg-7">
        @if($activeSession)
            {{-- Active Session Card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid var(--success) !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="d-inline-block px-3 py-1 rounded-pill mb-2" style="background-color: rgba(var(--secondary-rgb), 0.12); color: var(--success); font-size: 0.75rem; font-weight: 700;">
                                <span class="live-pulse-dot me-1" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: var(--success);"></span>
                                {{ __('messages.doctor_live_session') }}
                            </span>
                            <h5 class="fw-bold mb-1" style="color: var(--primary);">
                                <a href="{{ route('patients.show', $activeSession->patient_id) }}" class="text-decoration-none" style="color: var(--primary);" target="_blank">
                                    {{ $activeSession->patient->name }}
                                    <i class="fas fa-external-link-alt" style="font-size: 0.7rem;"></i>
                                </a>
                            </h5>
                            <small class="text-muted">{{ $activeSession->patient->patient_code ?? '' }} · {{ $activeSession->time->format('H:i') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="d-block fw-bold" style="color: var(--primary); font-size: 1.2rem;" id="sessionElapsed">00:00</span>
                            <small class="text-muted">{{ __('messages.doctor_elapsed') }}</small>
                        </div>
                    </div>

                    @if($activeSession->vital)
                    <div class="p-3 rounded mb-3" style="background-color: var(--cream);">
                        <div class="row g-2 text-center">
                            <div class="col-3">
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_bp') }}</span>
                                <span class="fw-semibold" style="color: var(--primary);">{{ $activeSession->vital->blood_pressure }}</span>
                            </div>
                            <div class="col-3">
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_temp') }}</span>
                                <span class="fw-semibold" style="color: var(--primary);">{{ $activeSession->vital->temperature }}°C</span>
                            </div>
                            <div class="col-3">
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_bmi') }}</span>
                                <span class="fw-semibold" style="color: var(--primary);">
                                    {{ $activeSession->vital->bmi ?? '--' }}
                                    @if($activeSession->vital->bmi_category)
                                        <span class="text-muted fw-normal">({{ ['Underweight' => __('messages.bmi_underweight'), 'Normal' => __('messages.bmi_normal'), 'Overweight' => __('messages.bmi_overweight'), 'Obese' => __('messages.bmi_obese')][$activeSession->vital->bmi_category] ?? $activeSession->vital->bmi_category }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="col-3">
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ __('messages.doctor_pulse') }}</span>
                                <span class="fw-semibold" style="color: var(--primary);">{{ $activeSession->vital->pulse ?? '--' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Session Vitals Request Button --}}
                    <form action="{{ route('doctor.appointments.session-vitals', $activeSession) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100" style="font-size: 0.8rem;">
                            <i class="fas fa-heartbeat me-1"></i>{{ __('messages.doctor_request_vitals') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Diagnosis & Notes Form --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3 px-4" style="background-color: var(--cream); border-bottom: 2px solid var(--primary);">
                    <h6 class="mb-0 fw-bold" style="color: var(--primary);">
                        <i class="fas fa-prescription me-2"></i>{{ __('messages.doctor_diagnosis_notes') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('doctor.appointments.complete', $activeSession) }}" method="POST" onsubmit="return confirm('{{ __('messages.doctor_complete_confirm') }}')">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold small" style="color: var(--primary);">{{ __('messages.doctor_diagnosis_label') }}</label>
                            <textarea name="diagnosis" rows="4" class="form-control" placeholder="{{ __('messages.doctor_diagnosis_placeholder') }}" style="border: 1px solid var(--border-medium); border-radius: 6px; resize: vertical;">{{ old('diagnosis', $activeSession->diagnosis) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small" style="color: var(--primary);">{{ __('messages.doctor_notes_label') }}</label>
                            <textarea name="notes" rows="6" class="form-control" placeholder="{{ __('messages.doctor_notes_placeholder') }}" style="border: 1px solid var(--border-medium); border-radius: 6px; resize: vertical;">{{ old('notes', $activeSession->notes) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn fw-bold px-4 btn-davinci-primary">
                                <i class="fas fa-check-double me-2"></i>{{ __('messages.doctor_complete_exam') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($readyQueue->isNotEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <i class="fas fa-hand-pointer text-muted mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    <h5 class="text-muted fw-normal">{{ __('messages.doctor_select_patient') }}</h5>
                    <p class="small text-muted">{{ __('messages.doctor_click_start') }}</p>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <i class="fas fa-check-circle mb-3" style="font-size: 2.5rem; color: var(--success); opacity: 0.5;"></i>
                    <h5 class="fw-normal" style="color: var(--primary);">{{ __('messages.doctor_all_clear') }}</h5>
                    <p class="small text-muted">{{ __('messages.doctor_no_patients') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    var elapsedInterval = null, waitingInterval = null;

    @if($activeSession && $activeSession->started_at)
    (function() {
        const startedAt = new Date("{{ $activeSession->started_at->toIso8601String() }}").getTime();
        const el = document.getElementById('sessionElapsed');

        function updateElapsed() {
            const diff = Math.floor((Date.now() - startedAt) / 1000);
            const m = Math.floor(diff / 60).toString().padStart(2, '0');
            const s = (diff % 60).toString().padStart(2, '0');
            if (el) el.textContent = m + ':' + s;
        }

        updateElapsed();
        elapsedInterval = setInterval(updateElapsed, 1000);
    })();
    @endif

    window.updateTimers = function updateTimers() {
        document.querySelectorAll('.waiting-timer').forEach(el => {
            const startTime = new Date(el.dataset.time).getTime();
            if (isNaN(startTime)) return;
            const diff = Math.floor((Date.now() - startTime) / 60000);
            el.textContent = diff + '{{ __('messages.doctor_minutes') }}';
            if (diff > 30) el.style.fontWeight = 'bold';
        });
    };
    window.updateTimers();
    waitingInterval = setInterval(window.updateTimers, 60000);

    window.addEventListener('beforeunload', function() {
        if (elapsedInterval) clearInterval(elapsedInterval);
        if (waitingInterval) clearInterval(waitingInterval);
        if (window.doctorPollInterval) clearInterval(window.doctorPollInterval);
    });

    window.refreshClinicalBoard = function refreshClinicalBoard() {
        $.get("{{ route('doctor.board-partial') }}", function(resp) {
            var $panel = $('#waitingQueuePanel');
            $panel.find('.card-body').fadeOut(120, function() {
                $(this).html(resp.html).fadeIn(120);
                $panel.find('.badge.rounded-pill.px-3').text(resp.count);
                if (typeof window.updateTimers === 'function') window.updateTimers();
            });
        });
    };

    window.doctorPollInterval = setInterval(window.refreshClinicalBoard, 15000);
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