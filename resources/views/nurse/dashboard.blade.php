@extends('layouts.dashboard')

@section('title', __('messages.dashboard'))
@section('page-title', __('messages.dashboard'))
@section('page-i18n', 'dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
<style>
/* ── Nurse-custom tokens (built on Da Vinci) ── */
.mat-btn {
    font-family: var(--font-body);
    font-weight: 600;
    font-size: var(--text-sm);
    letter-spacing: 0.02em;
    padding: var(--space-sm) var(--space-lg);
    border-radius: var(--radius-sm);
    border: 1px solid transparent;
    cursor: pointer;
    transition: background-color var(--duration-fast) var(--ease-out),
                box-shadow var(--duration-fast) var(--ease-out),
                opacity var(--duration-fast) var(--ease-out);
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    line-height: 1.4;
    text-decoration: none;
    white-space: nowrap;
}
.mat-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 2px var(--secondary);
}
.mat-btn-primary {
    background: var(--secondary);
    color: var(--white);
    box-shadow: var(--shadow-subtle);
}
.mat-btn-primary:hover {
    background: #0b2d2e;
    color: var(--white);
    box-shadow: var(--shadow-soft);
}
.mat-btn-primary:active {
    background: #081f20;
    box-shadow: none;
}
.mat-btn-ghost {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-light);
}
.mat-btn-ghost:hover {
    background: rgba(15, 61, 62, 0.06);
    color: var(--secondary);
    border-color: var(--border-medium);
}

/* Stat cards */
.stat-card { transition: box-shadow var(--duration-base) var(--ease-out); }
.stat-card:hover { box-shadow: var(--shadow-medium); }

.stat-icon {
    width: 40px; height: 40px;
    border-radius: var(--radius-full);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.stat-label {
    font-size: var(--text-xs);
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 2px;
}
.stat-value {
    font-size: 1.2rem;
    font-weight: 700;
    line-height: 1.2;
    color: var(--dark);
}
.stat-value-sm {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-secondary);
}

/* Table tweaks — Da Vinci overrides */
#triageTable thead th,
#waitingTable thead th {
    background: var(--cream);
    color: var(--text-secondary);
    font-weight: 600;
    font-size: var(--text-sm);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: var(--space-md) var(--space-lg);
    border-bottom: 1px solid var(--border-light);
}
#triageTable tbody td,
#waitingTable tbody td {
    padding: var(--space-md) var(--space-lg);
    border-bottom: 1px solid var(--border-hairline);
    vertical-align: middle;
    font-size: var(--text-base);
}
#triageTable tbody tr:hover,
#waitingTable tbody tr:hover {
    background: rgba(160, 82, 45, 0.03);
}
#triageTable tbody tr:last-child td,
#waitingTable tbody tr:last-child td {
    border-bottom: none;
}

/* Inline vital form */
.vital-input {
    border: 1px solid var(--border-light);
    border-radius: var(--radius-sm);
    padding: var(--space-sm) var(--space-md);
    font-family: var(--font-body);
    font-size: var(--text-sm);
    color: var(--text-primary);
    background: var(--input-bg);
    width: 100%;
    outline: none;
    transition: border-color var(--duration-fast) var(--ease-out),
                box-shadow var(--duration-fast) var(--ease-out);
}
.vital-input:focus {
    border-color: var(--secondary);
    box-shadow: 0 0 0 2px rgba(15, 61, 62, 0.15);
}
.vital-input::placeholder { color: var(--muted); }
.vital-label {
    font-size: var(--text-xs);
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-secondary);
    display: block;
    margin-bottom: var(--space-xs);
}

/* Live indicator */
.live-dot {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--success);
    animation: live-pulse 2s ease-in-out infinite;
}
@keyframes live-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* Empty state */
.empty-icon { color: var(--muted); opacity: 0.4; }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- ─── ROW: Flow Stats ─── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 card-border-flat stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon" style="background: rgba(46,93,52,0.12);">
                        <i class="fas fa-user-check" style="color: var(--success);"></i>
                    </div>
                    <div>
                        <div class="stat-label" data-i18n="checked_in">{{ __('messages.checked_in') }}</div>
                        <div class="stat-value" id="statTriageCount">{{ $triageQueue->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 card-border-flat stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon" style="background: rgba(191,140,48,0.12);">
                        <i class="fas fa-chair" style="color: var(--warning);"></i>
                    </div>
                    <div>
                        <div class="stat-label" data-i18n="waiting">{{ __('messages.waiting') }}</div>
                        <div class="stat-value" id="statWaitingCount">{{ $waitingList->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 card-border-flat stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon" style="background: rgba(15,61,62,0.10);">
                        <i class="fas fa-clock" style="color: var(--secondary);"></i>
                    </div>
                    <div>
                        <div class="stat-label" data-i18n="queue">{{ __('messages.queue') }}</div>
                        <div class="stat-value" id="statTotalToday">{{ $triageQueue->count() + $waitingList->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 card-border-flat stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon" style="background: var(--cream);">
                        <i class="fas fa-calendar-day" style="color: var(--text-secondary);"></i>
                    </div>
                    <div>
                        <div class="stat-label" data-i18n="date">{{ __('messages.date') }}</div>
                        <div class="stat-value-sm">{{ now()->format('Y-m-d') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── CARD: Live Triage Queue ─── --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 card-border-flat stat-card">
                <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" style="background: var(--white); border-bottom: 1px solid var(--border-hairline);">
                    <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: var(--text-lg); color: var(--dark);">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: rgba(15,61,62,0.08); border-radius: var(--radius-sm);">
                            <i class="fas fa-user-nurse" style="font-size: 0.9rem; color: var(--secondary);"></i>
                        </span>
                        <span data-i18n="triageQueue">{{ __('messages.triageQueue') }}</span>
                        <span class="live-dot ms-1" id="liveDot"></span>
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge" style="background: var(--secondary); color: var(--white); font-weight: 600;" id="triageBadge">{{ $triageQueue->count() }}</span>
                        <span class="text-muted small" id="lastUpdated">{{ now()->format('H:i') }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="triageTable" style="font-size: var(--text-sm);">
                            <thead>
                                <tr>
                                    <th class="ps-4" data-i18n="time">{{ __('messages.time') }}</th>
                                    <th data-i18n="patientName">{{ __('messages.patientName') }}</th>
                                    <th data-i18n="doctors">{{ __('messages.doctors') }}</th>
                                    <th data-i18n="status">{{ __('messages.status') }}</th>
                                    <th class="pe-4 text-center" style="width: 180px;" data-i18n="action">{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="triageQueueRows">
                                @forelse($triageQueue as $appt)
                                <tr data-appointment-id="{{ $appt->id }}">
                                    <td class="ps-4 text-nowrap" style="color: var(--text-secondary);">{{ $appt->time->format('H:i') }}</td>
                                    <td class="fw-semibold" style="color: var(--dark);">{{ $appt->patient->name }}</td>
                                    <td style="color: var(--text-secondary);">{{ $appt->doctor->name }}</td>
                                    <td>
                                        @if($appt->status === 'checked_in')
                                        <span class="badge bg-success">{{ __('messages.checked_in') }}</span>
                                        @else
                                        <span class="badge bg-info">{{ __('Confirmed') }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-center">
                                        <button type="button" class="mat-btn mat-btn-primary record-vitals-btn" data-appointment-id="{{ $appt->id }}" data-patient="{{ $appt->patient->name }}">
                                            <i class="fas fa-heartbeat"></i>
                                            <span data-i18n="recordVitals">{{ __('messages.recordVitals') }}</span>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="triageEmptyRow">
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-inbox d-block mb-2 empty-icon" style="font-size: 2rem;"></i>
                                        <span class="text-muted" data-i18n="waitingRoomEmpty">{{ __('messages.waitingRoomEmpty') }}</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── INLINE VITAL FORM ─── --}}
    <div class="row g-3 mb-4" id="vitalFormPanel" style="display: none;">
        <div class="col-12">
            <div class="card border-0 card-border-flat stat-card" style="border-left: 3px solid var(--secondary);">
                <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" style="background: var(--white); border-bottom: 1px solid var(--border-hairline);">
                    <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: var(--text-lg); color: var(--secondary);">
                        <i class="fas fa-heartbeat" style="font-size: 1rem;"></i>
                        <span data-i18n="vitalsForm">{{ __('messages.vitalsForm') }}</span>
                        <span id="vitalPatientName" class="fw-normal" style="color: var(--text-muted); font-size: var(--text-sm);"></span>
                    </h5>
                    <button type="button" id="vitalFormClose" class="btn-close" aria-label="Close"></button>
                </div>
                <div class="card-body p-4">
                    <form id="vitalsForm" method="POST">
                        @csrf
                        <input type="hidden" id="vitalAppointmentId" name="appointment_id" value="">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="vital-label">{{ __('messages.temperature') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" id="temperature" name="temperature" required min="30" max="45" placeholder="36.6" class="vital-input">
                            </div>
                            <div class="col-md-4">
                                <label class="vital-label">{{ __('messages.heartRate') }} <span class="text-danger">*</span></label>
                                <input type="number" id="pulse" name="pulse" required min="30" max="250" placeholder="72" class="vital-input">
                            </div>
                            <div class="col-md-4">
                                <label class="vital-label">{{ __('messages.bloodPressure') }} <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="number" name="bp_systolic" required min="50" max="250" placeholder="120" class="vital-input" style="text-align: center;">
                                    <span class="text-muted fw-semibold px-1">/</span>
                                    <input type="number" name="bp_diastolic" required min="30" max="150" placeholder="80" class="vital-input" style="text-align: center;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="vital-label">{{ __('messages.respiratoryRate') }}</label>
                                <input type="number" id="respiratory_rate" name="respiratory_rate" min="10" max="60" placeholder="16" class="vital-input">
                            </div>
                            <div class="col-md-4">
                                <label class="vital-label">{{ __('messages.weight') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" id="weight" name="weight" required min="1" max="500" placeholder="70.0" class="vital-input">
                            </div>
                            <div class="col-md-4">
                                <label class="vital-label">{{ __('messages.height') }}</label>
                                <input type="number" step="0.01" id="height" name="height" min="10" max="300" placeholder="175.0" class="vital-input">
                            </div>
                            <div class="col-md-4">
                                <label class="vital-label">{{ __('messages.oxygenSaturation') }}</label>
                                <input type="number" id="oxygen_saturation" name="oxygen_saturation" min="50" max="100" placeholder="98" class="vital-input">
                            </div>
                            <div class="col-md-4">
                                <label class="vital-label">BMI</label>
                                <div id="bmiDisplay" class="vital-input" style="background: var(--panel-bg); color: var(--text-muted); line-height: 2.2; cursor: default; border-style: dashed;">--</div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="vital-label">{{ __('messages.medicalNotes') }}</label>
                            <textarea id="notes" name="notes" rows="2" class="vital-input" placeholder="{{ __('messages.medicalNotes') }}">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid var(--border-hairline);">
                            <button type="button" id="vitalFormCancel" class="mat-btn mat-btn-ghost"><i class="fas fa-times me-1"></i>{{ __('messages.cancel') }}</button>
                            <button type="submit" class="mat-btn mat-btn-primary"><i class="fas fa-check-circle me-1"></i>{{ __('messages.approveAndSend') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── CARD: Waiting Room ─── --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 card-border-flat stat-card">
                <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" style="background: var(--white); border-bottom: 1px solid var(--border-hairline);">
                    <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: var(--text-lg); color: var(--dark);">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: rgba(191,140,48,0.12); border-radius: var(--radius-sm);">
                            <i class="fas fa-chair" style="font-size: 0.9rem; color: var(--warning);"></i>
                        </span>
                        <span data-i18n="waitingRoomReady">{{ __('Waiting Room (Ready for Doctor)') }}</span>
                    </h5>
                    <span class="badge" style="background: var(--warning); color: #fff; font-weight: 600;" id="waitingBadge">{{ $waitingList->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="waitingTable" style="font-size: var(--text-sm);">
                            <thead>
                                <tr>
                                    <th class="ps-4" data-i18n="time">{{ __('messages.time') }}</th>
                                    <th data-i18n="patientName">{{ __('messages.patientName') }}</th>
                                    <th data-i18n="doctors">{{ __('messages.doctors') }}</th>
                                    <th class="pe-4" data-i18n="status">{{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="waitingListRows">
                                @forelse($waitingList as $appt)
                                <tr>
                                    <td class="ps-4 text-nowrap" style="color: var(--text-secondary);">{{ $appt->time->format('H:i') }}</td>
                                    <td class="fw-semibold" style="color: var(--dark);">{{ $appt->patient->name }}</td>
                                    <td style="color: var(--text-secondary);">{{ $appt->doctor->name }}</td>
                                    <td class="pe-4"><span class="badge bg-warning">{{ __('messages.waiting') }}</span></td>
                                </tr>
                                @empty
                                <tr id="waitingEmptyRow">
                                    <td colspan="4" class="text-center py-5">
                                        <i class="fas fa-inbox d-block mb-2 empty-icon" style="font-size: 2rem;"></i>
                                        <span class="text-muted">{{ __('No patients waiting.') }}</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ─── BMI Compute ─── --}}
<script>
(function() {
    var w = document.getElementById('weight'), h = document.getElementById('height'), b = document.getElementById('bmiDisplay');
    function c() {
        var wv = parseFloat(w.value), hv = parseFloat(h.value);
        if (wv > 0 && hv > 0) {
            var bmi = wv / ((hv / 100) * (hv / 100));
            b.textContent = bmi.toFixed(1);
            b.style.color = bmi < 18.5 ? 'var(--info)' : bmi < 25 ? 'var(--success)' : bmi < 30 ? 'var(--warning)' : 'var(--danger)';
        } else { b.textContent = '--'; b.style.color = 'var(--text-muted)'; }
    }
    if (w) w.addEventListener('input', c); if (h) h.addEventListener('input', c);
})();
</script>

{{-- ─── AJAX Submit & Live Polling ─── --}}
<script>
(function() {
    'use strict';
    var poll = null;

    function openForm(id, name) {
        document.getElementById('vitalAppointmentId').value = id;
        document.getElementById('vitalPatientName').textContent = '\u2014 ' + name;
        document.getElementById('vitalFormPanel').style.display = 'block';
        document.getElementById('vitalFormPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    function closeForm() {
        document.getElementById('vitalFormPanel').style.display = 'none';
        document.getElementById('vitalsForm').reset();
        var b = document.getElementById('bmiDisplay');
        b.textContent = '--'; b.style.color = 'var(--text-muted)';
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.record-vitals-btn');
        if (btn) { e.preventDefault(); openForm(btn.dataset.appointmentId, btn.dataset.patient); }
    });

    document.getElementById('vitalFormClose').addEventListener('click', closeForm);
    document.getElementById('vitalFormCancel').addEventListener('click', closeForm);

    document.getElementById('vitalsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this, id = document.getElementById('vitalAppointmentId').value;
        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';

        var data = new URLSearchParams(new FormData(form));
        fetch('{{ url("appointments") }}/' + id + '/vitals/ajax', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: data
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (window.toast && typeof window.toast.show === 'function') window.toast.show(d.message, d.success ? 'success' : 'error');
            if (d.success) { closeForm(); refreshQueue(); }
        }).catch(function() {
            if (window.toast) window.toast.show('{{ __("messages.vitalsFailed") }}', 'error');
        }).finally(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>{{ __("messages.approveAndSend") }}';
        });
    });

    function refreshQueue() {
        fetch('{{ route("api.nurse.triage-queue") }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            render('triageQueueRows', d.triageQueue, true);
            render('waitingListRows', d.waitingList, false);
            document.getElementById('triageBadge').textContent = d.triageCount;
            document.getElementById('waitingBadge').textContent = d.waitingCount;
            document.getElementById('statTriageCount').textContent = d.triageCount;
            document.getElementById('statWaitingCount').textContent = d.waitingCount;
            document.getElementById('statTotalToday').textContent = d.triageCount + d.waitingCount;
            document.getElementById('lastUpdated').textContent = new Date().toTimeString().slice(0,5);
        }).catch(function() {});
    }

    function render(id, items, act) {
        var tbody = document.getElementById(id);
        if (!items.length) {
            var c = act ? 5 : 4;
            tbody.innerHTML = '<tr><td colspan="' + c + '" class="text-center py-5"><i class="fas fa-inbox d-block mb-2 empty-icon" style="font-size: 2rem;"></i><span class="text-muted">{{ __("messages.waitingRoomEmpty") }}</span></td></tr>';
            return;
        }
        var h = '';
        items.forEach(function(a) {
            var s = a.status === 'checked_in'
                ? '<span class="badge bg-success">{{ __("messages.checked_in") }}</span>'
                : '<span class="badge bg-info">{{ __("Confirmed") }}</span>';
            var r = act
                ? '<button type="button" class="mat-btn mat-btn-primary record-vitals-btn" data-appointment-id="' + a.id + '" data-patient="' + esc(a.patient_name) + '"><i class="fas fa-heartbeat"></i> {{ __("messages.recordVitals") }}</button>'
                : '<span class="badge bg-warning">{{ __("messages.waiting") }}</span>';
            h += '<tr><td class="ps-4 text-nowrap" style="color: var(--text-secondary);">' + a.time + '</td><td class="fw-semibold" style="color: var(--dark);">' + esc(a.patient_name) + '</td><td style="color: var(--text-secondary);">' + esc(a.doctor_name) + '</td><td>' + s + '</td>' + (act ? '<td class="pe-4 text-center">' + r + '</td>' : '<td class="pe-4">' + r + '</td>') + '</tr>';
        });
        tbody.innerHTML = h;
    }
    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s)); return d.innerHTML; }

    window.refreshTriageBoard = refreshQueue;

    poll = setInterval(refreshQueue, 15000);
    window.nursePollInterval = poll;
})();
</script>
@endsection
