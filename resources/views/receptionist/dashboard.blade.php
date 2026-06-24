@extends('layouts.dashboard')

@section('title', __('messages.receptionistDashboard'))
@section('page-title', __('messages.receptionistDashboard'))
@section('page-i18n', 'receptionistDashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
<style>
    /* ─── Receptionist Dashboard — Da Vinci Identity ─── */
    .recep-header-icon {
        width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: var(--radius-sm);
        background: var(--secondary);
        color: var(--white);
        font-size: var(--text-sm);
        flex-shrink: 0;
    }
    .recep-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-hairline);
        box-shadow: var(--shadow-soft);
        transition: box-shadow var(--duration-base) var(--ease-out);
    }
    .recep-card:hover { box-shadow: var(--shadow-medium); }
    .recep-card-header {
        padding: var(--space-lg) var(--space-xl);
        border-bottom: 1px solid var(--border-hairline);
        display: flex; justify-content: space-between; align-items: center;
    }
    .recep-card-title {
        font-family: var(--font-heading);
        font-size: var(--text-base);
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex; align-items: center; gap: var(--space-md);
    }
    .recep-card-body { padding: 0; }

    /* Table — Da Vinci */
    .recep-table {
        width: 100%;
        font-size: var(--text-base);
        border-collapse: collapse;
    }
    .recep-table thead {
        background: var(--cream);
        border-bottom: 1px solid var(--border-light);
    }
    .recep-table thead th {
        padding: var(--space-lg) var(--space-xl);
        font-family: var(--font-heading);
        font-size: var(--text-sm);
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }
    .recep-table thead th:first-child { padding-left: var(--space-xl); }
    .recep-table thead th:last-child { padding-right: var(--space-xl); }
    .recep-table tbody tr {
        border-bottom: 1px solid var(--border-hairline);
        transition: background-color var(--duration-fast) var(--ease-out);
    }
    .recep-table tbody tr:last-child { border-bottom: none; }
    .recep-table tbody tr:hover { background: rgba(15, 61, 62, 0.03); }
    .recep-table tbody td {
        padding: var(--space-lg) var(--space-xl);
        vertical-align: middle;
    }
    .recep-table tbody td:first-child { padding-left: var(--space-xl); }
    .recep-table tbody td:last-child { padding-right: var(--space-xl); }

    /* Patient link in triage */
    .recep-patient-name {
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-ar);
    }
    .recep-patient-phone {
        font-size: var(--text-xs);
        color: var(--light-text);
        direction: ltr;
        display: inline-block;
    }

    /* Status Badges — Da Vinci semantic palette */
    .badge-dv {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 12px;
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        font-weight: 600;
        white-space: nowrap;
    }
    .badge-dv-pending {
        background: rgba(191, 140, 48, 0.12);
        color: var(--warning);
    }
    .badge-dv-confirmed {
        background: rgba(61, 90, 128, 0.12);
        color: var(--info);
    }
    .badge-dv-scheduled {
        background: rgba(15, 61, 62, 0.10);
        color: var(--secondary);
    }
    .badge-dv-checkedin {
        background: rgba(46, 93, 52, 0.12);
        color: var(--success);
    }
    .badge-dv-waiting {
        background: rgba(191, 140, 48, 0.12);
        color: var(--warning);
    }
    .badge-dv-progress {
        background: rgba(61, 90, 128, 0.12);
        color: var(--info);
    }
    .badge-dv-completed {
        background: rgba(46, 93, 52, 0.12);
        color: var(--success);
    }
    .badge-dv-cancelled {
        background: rgba(139, 58, 58, 0.10);
        color: var(--danger);
    }
    .badge-dv-noshow {
        background: rgba(139, 58, 58, 0.10);
        color: var(--danger);
    }
    .badge-dv-type {
        background: var(--cream);
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* Action buttons — Da Vinci */
    .dv-btn-primary {
        display: inline-flex; align-items: center; gap: var(--space-sm);
        padding: var(--space-sm) var(--space-lg);
        background: var(--secondary);
        color: var(--white);
        border: none;
        border-radius: var(--radius-sm);
        font-family: var(--font-ar);
        font-size: var(--text-sm);
        font-weight: 500;
        cursor: pointer;
        transition: background-color var(--duration-fast) var(--ease-out),
                    transform var(--duration-fast) var(--ease-out),
                    box-shadow var(--duration-fast) var(--ease-out);
        text-decoration: none;
    }
    .dv-btn-primary:hover {
        background: var(--secondary-light);
        color: var(--white);
        transform: translateY(-1px);
        box-shadow: var(--shadow-subtle);
    }
    .dv-btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .dv-btn-subtle {
        display: inline-flex; align-items: center; gap: var(--space-sm);
        padding: var(--space-sm) var(--space-lg);
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-sm);
        font-family: var(--font-en);
        font-size: var(--text-sm);
        font-weight: 500;
        cursor: pointer;
        transition: all var(--duration-fast) var(--ease-out);
        text-decoration: none;
    }
    .dv-btn-subtle:hover {
        background: var(--cream);
        color: var(--dark);
        border-color: var(--border-medium);
        text-decoration: none;
    }
    .dv-btn-subtle.danger-hover:hover {
        background: rgba(139, 58, 58, 0.08);
        color: var(--danger);
        border-color: rgba(139, 58, 58, 0.2);
    }
    .dv-btn-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all var(--duration-fast) var(--ease-out);
        text-decoration: none;
        font-size: var(--text-sm);
    }
    .dv-btn-icon:hover {
        background: var(--cream);
        color: var(--danger);
        border-color: rgba(139, 58, 58, 0.2);
    }

    /* Flow Monitor — Golden ratio stat cards */
    .flow-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border-hairline);
        padding: var(--space-xl);
        transition: box-shadow var(--duration-base) var(--ease-out),
                    transform var(--duration-base) var(--ease-out);
    }
    .flow-card:hover {
        box-shadow: var(--shadow-soft);
        transform: translateY(-2px);
    }
    .flow-icon {
        width: 44px; height: 44px;
        border-radius: var(--radius);
        display: flex; align-items: center; justify-content: center;
        font-size: var(--text-lg);
        flex-shrink: 0;
    }
    .flow-icon-checkedin  { background: rgba(46, 93, 52, 0.10); color: var(--success); }
    .flow-icon-waiting    { background: rgba(191, 140, 48, 0.10); color: var(--warning); }
    .flow-icon-progress   { background: rgba(61, 90, 128, 0.10); color: var(--info); }
    .flow-icon-completed  { background: rgba(46, 93, 52, 0.10); color: var(--success); }
    .flow-label {
        font-size: var(--text-xs);
        font-weight: 600;
        color: var(--light-text);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }
    .flow-value {
        font-family: var(--font-heading);
        font-size: var(--text-2xl);
        font-weight: 700;
        color: var(--dark);
        line-height: 1.1;
    }
    .flow-sub-row {
        display: flex;
        gap: 0;
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--border-hairline);
    }
    .flow-sub-item {
        flex: 1;
        text-align: center;
        padding: var(--space-lg);
        background: var(--card-bg);
        border-right: 1px solid var(--border-hairline);
    }
    .flow-sub-item:last-child { border-right: none; }
    .flow-sub-label {
        font-size: var(--text-xs);
        font-weight: 600;
        color: var(--light-text);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: var(--space-xs);
    }
    .flow-sub-value {
        font-family: var(--font-heading);
        font-size: var(--text-lg);
        font-weight: 700;
    }
    .flow-sub-value.cancelled { color: var(--danger); }
    .flow-sub-value.noshow   { color: var(--danger); }
    .flow-sub-value.pending  { color: var(--dark); }

    /* Count badge on card header */
    .dv-count-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 22px; height: 22px;
        padding: 0 6px;
        background: var(--dark);
        color: var(--white);
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        font-weight: 700;
        font-family: var(--font-en);
    }

    /* Empty state */
    .dv-empty {
        text-align: center;
        padding: var(--space-3xl) var(--space-xl);
    }
    .dv-empty-icon {
        font-size: 2.5rem;
        color: var(--stone);
        margin-bottom: var(--space-lg);
        display: block;
    }
    .dv-empty-text {
        font-size: var(--text-base);
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* Last active badge for live table */
    .dv-since {
        font-size: var(--text-xs);
        color: var(--light-text);
    }

    /* RTL overrides for table padding */
    [dir="rtl"] .recep-table thead th:first-child { padding-left: var(--space-lg); padding-right: var(--space-xl); }
    [dir="rtl"] .recep-table thead th:last-child { padding-right: var(--space-lg); padding-left: var(--space-xl); }
    [dir="rtl"] .recep-table tbody td:first-child { padding-left: var(--space-lg); padding-right: var(--space-xl); }
    [dir="rtl"] .recep-table tbody td:last-child { padding-right: var(--space-lg); padding-left: var(--space-xl); }
    [dir="rtl"] .dv-btn-primary { font-family: var(--font-ar); }
    [dir="rtl"] .flow-sub-item { border-right: none; border-left: 1px solid var(--border-hairline); }
    [dir="rtl"] .flow-sub-item:last-child { border-left: none; }

    /* Row fade-out for AJAX check-in */
    .row-fadeout {
        transition: opacity 0.3s var(--ease-out), transform 0.3s var(--ease-out);
    }

    /* Dark theme overrides */
    [data-theme="dark"] .recep-card { background: var(--card-bg); }
    [data-theme="dark"] .recep-table thead { background: var(--cream); }
    [data-theme="dark"] .recep-table tbody tr:hover { background: rgba(45, 184, 148, 0.04); }
    [data-theme="dark"] .dv-btn-primary { background: var(--secondary); color: var(--white); }
    [data-theme="dark"] .dv-btn-primary:hover { background: var(--secondary-light); }
    [data-theme="dark"] .dv-btn-subtle { color: var(--text-secondary); border-color: var(--border-light); }
    [data-theme="dark"] .dv-btn-subtle:hover { background: var(--cream); color: var(--text-primary); }
    [data-theme="dark"] .badge-dv-type { background: var(--cream); color: var(--text-secondary); }
    [data-theme="dark"] .flow-card { background: var(--card-bg); }
    [data-theme="dark"] .flow-sub-item { background: var(--card-bg); }
    [data-theme="dark"] .dv-empty-icon { color: var(--stone); }
</style>
@endsection

@section('content')
@if(auth()->user()->hasRole('receptionist'))

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION A: جدول مواعيد اليوم — Active Triage Board
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="recep-card">
            <div class="recep-card-header">
                <h5 class="recep-card-title">
                    <span class="recep-header-icon"><i class="fas fa-clipboard-list"></i></span>
                    <span>{{ __('messages.activeTriageBoard') }}</span>
                    <span class="dv-count-badge">{{ count($triageBoard) }}</span>
                </h5>
                <span class="text-secondary" style="font-size: var(--text-sm); font-weight: 500; direction: ltr; display: inline-block;">{{ now()->format('M d, Y') }}</span>
            </div>
            <div class="recep-card-body">
                <div class="table-responsive">
                    <table class="recep-table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.time') }}</th>
                                <th>{{ __('messages.patient') }}</th>
                                <th>{{ __('messages.doctor') }}</th>
                                <th>{{ __('messages.type') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th class="text-center" style="width: 340px;">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="triage-board-body">
                            @forelse($triageBoard as $appt)
                            <tr>
                                <td class="text-nowrap" style="font-weight: 600; color: var(--dark); font-family: var(--font-en);">{{ $appt->time->format('H:i') }}</td>
                                <td>
                                    <div class="recep-patient-name">{{ $appt->patient->name ?? __('messages.patient') }}</div>

                                </td>
                                <td style="color: var(--text-secondary);">{{ $appt->doctor->name ?? __('messages.doctor') }}</td>
                                <td><span class="badge-dv badge-dv-type">{{ $appt->type === 'Checkup' ? __('messages.checkup') : ($appt->type === 'استشارة' ? __('messages.consultation') : $appt->type) }}</span></td>
                                <td>
                                    @php
                                        $badgeClass = 'badge-dv-pending';
                                        $statusKey = 'messages.pending';
                                        if ($appt->status === 'confirmed') { $badgeClass = 'badge-dv-confirmed'; $statusKey = 'messages.confirmed'; }
                                        elseif ($appt->status === 'scheduled') { $badgeClass = 'badge-dv-scheduled'; $statusKey = 'messages.scheduled'; }
                                    @endphp
                                    <span class="badge-dv {{ $badgeClass }}">
                                        <i class="fas fa-circle" style="font-size: 5px;"></i>
                                        {{ __($statusKey) }}
                                    </span>
                                </td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <div class="d-flex flex-row align-items-center justify-content-center flex-nowrap gap-2" style="min-width: 320px;">
                                        <form action="{{ route('receptionist.check-in', $appt->id) }}" method="POST" class="d-inline js-check-in-form">
                                            @csrf
                                            <button type="submit" class="dv-btn-primary" title="{{ __('messages.checkIn') }}" style="white-space: nowrap;">
                                                <i class="fas fa-check-circle"></i>
                                                <span>{{ __('messages.checkIn') }}</span>
                                            </button>
                                        </form>
                                        <a href="{{ route('appointments.edit', $appt->id) }}" class="dv-btn-subtle" title="{{ __('messages.editAppt') }}" style="white-space: nowrap;">
                                            <i class="fas fa-edit"></i>
                                            <span>{{ __('messages.editAppt') }}</span>
                                        </a>
                                        @if(in_array($appt->status, ['checked_in', 'completed']))
                                        <a href="{{ route('invoices.create-from-appointment', $appt->id) }}" class="dv-btn-subtle" title="{{ __('messages.createInvoice') }}" style="white-space: nowrap;">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                            <span>{{ __('messages.createInvoice') }}</span>
                                        </a>
                                        @endif
                                        <form action="{{ route('receptionist.no-show', $appt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirmNoShow') }}')">
                                            @csrf
                                            <button type="submit" class="dv-btn-icon" title="{{ __('messages.no_show') }}">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr class="empty-row">
                                <td colspan="6">
                                    <div class="dv-empty">
                                        <i class="fas fa-inbox dv-empty-icon"></i>
                                        <span class="dv-empty-text">{{ __('messages.noPendingArrivals') }}</span>
                                    </div>
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

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION B: تدفق المرضى — Live Patient Flow Monitor
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Left column: Flow stat cards --}}
    <div class="col-md-5">
        <div class="recep-card">
            <div class="recep-card-header">
                <h6 class="recep-card-title" style="font-size: var(--text-sm);">
                    <span class="recep-header-icon" style="width: 28px; height: 28px; font-size: var(--text-xs);"><i class="fas fa-people-arrows"></i></span>
                    <span>{{ __('messages.patientFlow') }}</span>
                </h6>
            </div>
            <div class="recep-card-body" style="padding: var(--space-xl);">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flow-icon flow-icon-checkedin"><i class="fas fa-user-check"></i></div>
                            <div>
                                <div class="flow-label">{{ __('messages.checked_in') }}</div>
                                <div class="flow-value" id="flow-checked-in">{{ $flowMonitor['checked_in'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flow-icon flow-icon-waiting"><i class="fas fa-chair"></i></div>
                            <div>
                                <div class="flow-label">{{ __('messages.waiting') }}</div>
                                <div class="flow-value" id="flow-waiting">{{ $flowMonitor['waiting'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flow-icon flow-icon-progress"><i class="fas fa-stethoscope"></i></div>
                            <div>
                                <div class="flow-label">{{ __('messages.withDoctor') }}</div>
                                <div class="flow-value" id="flow-in-progress">{{ $flowMonitor['in_progress'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flow-icon flow-icon-completed"><i class="fas fa-check-double"></i></div>
                            <div>
                                <div class="flow-label">{{ __('messages.completed') }}</div>
                                <div class="flow-value" id="flow-completed">{{ $flowMonitor['completed'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flow-sub-row mt-3">
            <div class="flow-sub-item">
                <div class="flow-sub-label">{{ __('messages.cancelled') }}</div>
                <div class="flow-sub-value cancelled">{{ $flowMonitor['cancelled'] }}</div>
            </div>
            <div class="flow-sub-item">
                <div class="flow-sub-label">{{ __('messages.no_show') }}</div>
                <div class="flow-sub-value noshow">{{ $flowMonitor['no_show'] }}</div>
            </div>
            <div class="flow-sub-item">
                <div class="flow-sub-label">{{ __('messages.pending') }}</div>
                <div class="flow-sub-value pending">{{ count($triageBoard) }}</div>
            </div>
        </div>
    </div>

    {{-- Right column: Live patient table --}}
    <div class="col-md-7">
        <div class="recep-card">
            <div class="recep-card-header">
                <h6 class="recep-card-title" style="font-size: var(--text-sm);">
                    <span class="recep-header-icon" style="width: 28px; height: 28px; font-size: var(--text-xs);"><i class="fas fa-sync-alt"></i></span>
                    <span>{{ __('messages.livePatientFlow') }}</span>
                </h6>
                <span class="dv-count-badge">{{ count($livePatients) }} <span  style="font-weight: 500;">{{ __('messages.active') }}</span></span>
            </div>
            <div class="recep-card-body">
                <div class="table-responsive">
                    <table class="recep-table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.patient') }}</th>
                                <th>{{ __('messages.doctor') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.since') }}</th>
                            </tr>
                        </thead>
                        <tbody id="live-patients-tbody">
                            @forelse($livePatients as $appt)
                            <tr>
                                <td class="recep-patient-name">{{ $appt->patient->name ?? __('messages.patient') }}</td>
                                <td style="color: var(--text-secondary);">{{ $appt->doctor->name ?? __('messages.doctor') }}</td>
                                <td>
                                    @php
                                        $fbClass = 'badge-dv-checkedin';
                                        $fbIcon = 'fa-user-check';
                                        $fbKey = 'messages.checked_in';
                                        if ($appt->status === 'waiting') { $fbClass = 'badge-dv-waiting'; $fbIcon = 'fa-chair'; $fbKey = 'messages.waiting'; }
                                        elseif ($appt->status === 'in_progress') { $fbClass = 'badge-dv-progress'; $fbIcon = 'fa-play-circle'; $fbKey = 'messages.in_progress'; }
                                    @endphp
                                    <span class="badge-dv {{ $fbClass }}">
                                        <i class="fas {{ $fbIcon }}" style="font-size: 0.65rem;"></i>
                                        {{ __($fbKey) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="dv-since">
                                        @if($appt->checked_in_at)
                                            {{ $appt->checked_in_at->diffForHumans() }}
                                        @elseif($appt->started_at)
                                            {{ $appt->started_at->diffForHumans() }}
                                        @else
                                            {{ $appt->time->format('H:i') }}
                                        @endif
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr class="empty-row">
                                <td colspan="4">
                                    <div class="dv-empty">
                                        <i class="fas fa-bed dv-empty-icon"></i>
                                        <span class="dv-empty-text">{{ __('messages.noActivePatients') }}</span>
                                    </div>
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

@section('scripts')
@parent
<script>
window.refreshReceptionBoard = function() {
    $.getJSON("{{ route('receptionist.board-data') }}", function(resp) {
        $('#flow-checked-in').text(resp.flowMonitor.checked_in);
        $('#flow-waiting').text(resp.flowMonitor.waiting);
        $('#flow-in-progress').text(resp.flowMonitor.in_progress);
        $('#flow-completed').text(resp.flowMonitor.completed);
        $('.flow-sub-value.cancelled').text(resp.flowMonitor.cancelled);
        $('.flow-sub-value.noshow').text(resp.flowMonitor.no_show);
        $('#live-patients-tbody').html(resp.html);
    });
};

$(function() {
    var forms = document.querySelectorAll('.js-check-in-form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    var row = form.closest('tr');
                    row.classList.add('row-fadeout');
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';

                    var checkedInEl = document.getElementById('flow-checked-in');
                    if (checkedInEl) {
                        checkedInEl.textContent = parseInt(checkedInEl.textContent) + 1;
                    }

                    var liveTbody = document.getElementById('live-patients-tbody');
                    if (liveTbody) {
                        var emptyRow = liveTbody.querySelector('.empty-row');
                        if (emptyRow) emptyRow.remove();

                        var p = data.appointment && data.appointment.patient ? data.appointment.patient.name : 'N/A';
                        var d = data.appointment && data.appointment.doctor ? data.appointment.doctor.name : 'N/A';
                        var newRow = document.createElement('tr');
                        newRow.innerHTML = '<td class="recep-patient-name">' + p + '</td>' +
                            '<td style="color: var(--text-secondary);">' + d + '</td>' +
                            '<td><span class="badge-dv badge-dv-checkedin"><i class="fas fa-user-check" style="font-size: 0.65rem;"></i> ' + '{{ __("messages.checked_in") }}' + '</span></td>' +
                            '<td><span class="dv-since">{{ __("messages.justNow") }}</span></td>';
                        liveTbody.appendChild(newRow);
                    }

                    setTimeout(function() {
                        row.remove();
                        var triageBody = document.getElementById('triage-board-body');
                        if (triageBody && triageBody.querySelectorAll('tr:not(.empty-row)').length === 0) {
                            var empty = triageBody.querySelector('.empty-row');
                            if (!empty) {
                                triageBody.innerHTML = '<tr class="empty-row"><td colspan="6"><div class="dv-empty">' +
                                    '<i class="fas fa-inbox dv-empty-icon"></i>' +
                                    '<span class="dv-empty-text">{{ __("messages.noPendingArrivals") }}</span></div></td></tr>';
                            }
                        }
                        if (typeof window.refreshReceptionBoard === 'function') {
                            window.refreshReceptionBoard();
                        }
                    }, 300);
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    });
});
</script>
@endsection

@else
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="recep-card">
            <div class="recep-card-body">
                <div class="dv-empty">
                    <i class="fas fa-lock dv-empty-icon"></i>
                    <h5 class="dv-empty-text mb-3">{{ __('messages.receptionistOnly') }}</h5>
                    <a href="{{ route('dashboard') }}" class="dv-btn-primary d-inline-flex text-decoration-none">
                        <i class="fas fa-arrow-left"></i>
                        <span>{{ __('messages.backToMainDash') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
