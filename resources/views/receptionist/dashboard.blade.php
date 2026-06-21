@extends('layouts.dashboard')

@section('title', __('messages.receptionistDashboard'))
@section('page-title', __('messages.receptionistDashboard'))
@section('page-i18n', 'receptionistDashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
<style>
    .matte-btn {
        background-color: #1a1a2e;
        color: #f5f0e8;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.375rem 0.875rem;
        transition: opacity 0.15s ease;
        cursor: pointer;
    }
    .matte-btn:hover { opacity: 0.85; color: #f5f0e8; }
    .matte-btn-subtle {
        background-color: #f5f0e8;
        color: #8b3a3a;
        border: 1px solid #e0d8cc;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 400;
        padding: 0.375rem 0.75rem;
        transition: opacity 0.15s ease;
        cursor: pointer;
    }
    .matte-btn-subtle:hover { opacity: 0.75; }
    .flow-stat-label { font-size: 0.7rem; letter-spacing: 0.3px; text-transform: uppercase; color: #6c757d; margin-bottom: 0.15rem; }
    .flow-stat-value { font-size: 1.25rem; font-weight: 700; line-height: 1.2; color: #1a1a2e; }
    .badge-matte-pending { background-color: #fff3cd; color: #856404; font-weight: 500; }
    .badge-matte-confirmed { background-color: #cce5ff; color: #004085; font-weight: 500; }
    .badge-matte-checkedin { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-matte-waiting { background-color: #fff3cd; color: #856404; font-weight: 500; }
    .badge-matte-progress { background-color: #cce5ff; color: #004085; font-weight: 500; }
    .badge-matte-completed { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-matte-cancelled { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    .badge-matte-noshow { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    .section-header-icon { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
    .card-border-flat { border: 1px solid #e8e2d8; border-radius: 10px; }
    .divider-flat { border: none; border-top: 1px solid #ece7e0; margin: 0; }
</style>
@endsection

@section('content')
@if(auth()->user()->hasRole('receptionist'))

{{-- SECTION A: ACTIVE TRIAGE BOARD --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm card-border-flat">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                <h5 class="mb-0 fw-semibold" style="font-size: 0.95rem;">
                    <span class="section-header-icon bg-light text-dark me-2"><i class="fas fa-clipboard-list" style="font-size: 0.75rem;"></i></span>
                    <span data-i18n="activeTriageBoard">{{ __('Active Triage Board') }}</span>
                    <span class="badge bg-dark ms-2" style="font-weight: 500;">{{ count($triageBoard) }}</span>
                </h5>
                <span class="text-muted small">{{ now()->format('M d, Y') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" data-i18n="timeTable">{{ __('Time') }}</th>
                                <th data-i18n="patientName">{{ __('Patient') }}</th>
                                <th data-i18n="doctor">{{ __('Doctor') }}</th>
                                <th data-i18n="type">{{ __('Type') }}</th>
                                <th data-i18n="status">{{ __('Status') }}</th>
                                <th class="pe-4 text-center" data-i18n="action" style="width: 280px;">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($triageBoard as $appt)
                            <tr>
                                <td class="ps-4 text-nowrap">{{ $appt->time->format('H:i') }}</td>
                                <td class="fw-medium">
                                    {{ $appt->patient->name ?? __('N/A') }}
                                    @if($appt->patient && $appt->patient->phone)
                                    <br><small class="text-muted" style="font-size: 0.75rem;">{{ $appt->patient->phone }}</small>
                                    @endif
                                </td>
                                <td>{{ $appt->doctor->name ?? __('N/A') }}</td>
                                <td><span class="badge bg-light text-dark px-3 py-1 fw-normal">{{ $appt->type }}</span></td>
                                <td>
                                    @php
                                        $matteBadge = '';
                                        if ($appt->status === 'pending') $matteBadge = 'badge-matte-pending';
                                        elseif ($appt->status === 'confirmed') $matteBadge = 'badge-matte-confirmed';
                                        elseif ($appt->status === 'scheduled') $matteBadge = 'badge-matte-pending';
                                    @endphp
                                    <span class="badge rounded-pill px-3 py-1 {{ $matteBadge }}">
                                        {{ $appt->status === 'scheduled' ? __('Scheduled') : ucfirst(__($appt->status)) }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <form action="{{ route('receptionist.check-in', $appt->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="matte-btn d-inline-flex align-items-center gap-1">
                                                <i class="fas fa-check-circle"></i>
                                                <span data-i18n="checkIn">{{ __('Check-In') }}</span>
                                                <span style="font-family: 'Tajawal', sans-serif;"> / وصول المريض</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('receptionist.no-show', $appt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Mark this patient as No Show?') }}')">
                                            @csrf
                                            <button type="submit" class="matte-btn-subtle d-inline-flex align-items-center gap-1">
                                                <i class="fas fa-user-slash"></i>
                                                <span data-i18n="noShow">{{ __('No Show') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox d-block mb-3" style="font-size: 2rem; color: #d0c8bc;"></i>
                                    <span data-i18n="noPendingArrivals">{{ __('All clear — no pending arrivals for today.') }}</span>
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

{{-- SECTION B: LIVE PATIENT FLOW MONITOR --}}
<div class="row g-3 mb-4">

    {{-- Left column: Flow stat cards --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm card-border-flat mb-3">
            <div class="card-body px-4 py-3">
                <h6 class="text-muted mb-3 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.8rem; letter-spacing: 0.5px; text-transform: uppercase;">
                    <i class="fas fa-people-arrows"></i>
                    <span data-i18n="patientFlow">{{ __('Patient Flow') }}</span>
                </h6>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #d4edda;">
                                <i class="fas fa-user-check text-success"></i>
                            </div>
                            <div>
                                <div class="flow-stat-label" data-i18n="checkedIn">{{ __('Checked In') }}</div>
                                <div class="flow-stat-value">{{ $flowMonitor['checked_in'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #fff3cd;">
                                <i class="fas fa-chair text-warning"></i>
                            </div>
                            <div>
                                <div class="flow-stat-label" data-i18n="waiting">{{ __('Waiting') }}</div>
                                <div class="flow-stat-value">{{ $flowMonitor['waiting'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #cce5ff;">
                                <i class="fas fa-stethoscope text-info"></i>
                            </div>
                            <div>
                                <div class="flow-stat-label" data-i18n="withDoctor">{{ __('With Doctor') }}</div>
                                <div class="flow-stat-value">{{ $flowMonitor['in_progress'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #f0f0f0;">
                                <i class="fas fa-check-double text-secondary"></i>
                            </div>
                            <div>
                                <div class="flow-stat-label" data-i18n="completed">{{ __('Completed') }}</div>
                                <div class="flow-stat-value">{{ $flowMonitor['completed'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm card-border-flat">
            <div class="card-body px-4 py-3">
                <div class="row text-center g-0">
                    <div class="col-4">
                        <div class="flow-stat-label" data-i18n="cancelled">{{ __('Cancelled') }}</div>
                        <span class="fw-bold" style="color: #721c24; font-size: 1.1rem;">{{ $flowMonitor['cancelled'] }}</span>
                    </div>
                    <div class="col-4">
                        <div class="flow-stat-label" data-i18n="noShow">{{ __('No Show') }}</div>
                        <span class="fw-bold" style="color: #721c24; font-size: 1.1rem;">{{ $flowMonitor['no_show'] }}</span>
                    </div>
                    <div class="col-4">
                        <div class="flow-stat-label" data-i18n="pendingArrival">{{ __('Pending') }}</div>
                        <span class="fw-bold" style="color: #1a1a2e; font-size: 1.1rem;">{{ count($triageBoard) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right column: Live patient table --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm card-border-flat">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                    <span class="section-header-icon bg-light text-dark"><i class="fas fa-sync-alt" style="font-size: 0.75rem;"></i></span>
                    <span data-i18n="livePatientFlow">{{ __('Live Patient Flow') }}</span>
                </h6>
                <span class="badge bg-dark" style="font-weight: 500;">{{ count($livePatients) }} <span data-i18n="active">{{ __('active') }}</span></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" data-i18n="patientName">{{ __('Patient') }}</th>
                                <th data-i18n="doctor">{{ __('Doctor') }}</th>
                                <th data-i18n="status">{{ __('Status') }}</th>
                                <th class="pe-4" data-i18n="since">{{ __('Since') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($livePatients as $appt)
                            <tr>
                                <td class="ps-4 fw-medium">{{ $appt->patient->name ?? __('N/A') }}</td>
                                <td>{{ $appt->doctor->name ?? __('N/A') }}</td>
                                <td>
                                    @php
                                        $fbClass = 'badge-matte-checkedin';
                                        $fbIcon = 'fa-user-check';
                                        if ($appt->status === 'waiting') { $fbClass = 'badge-matte-waiting'; $fbIcon = 'fa-chair'; }
                                        elseif ($appt->status === 'in_progress') { $fbClass = 'badge-matte-progress'; $fbIcon = 'fa-play-circle'; }
                                    @endphp
                                    <span class="badge rounded-pill px-3 py-1 {{ $fbClass }}">
                                        <i class="fas {{ $fbIcon }} me-1"></i>
                                        {{ $appt->status === 'checked_in' ? __('messages.checked_in') : ucfirst(__($appt->status)) }}
                                    </span>
                                </td>
                                <td class="pe-4 text-muted">
                                    @if($appt->checked_in_at)
                                        {{ $appt->checked_in_at->diffForHumans() }}
                                    @elseif($appt->started_at)
                                        {{ $appt->started_at->diffForHumans() }}
                                    @else
                                        {{ $appt->time->format('H:i') }}
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-bed d-block mb-3" style="font-size: 2rem; color: #d0c8bc;"></i>
                                    <span data-i18n="noActivePatients">{{ __('No active patients in the flow right now.') }}</span>
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

@else
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm card-border-flat">
            <div class="card-body text-center py-5">
                <i class="fas fa-lock d-block mb-3" style="font-size: 2.5rem; color: #d0c8bc;"></i>
                <h5 class="text-muted" data-i18n="receptionistOnly">{{ __('This dashboard is for receptionist use only.') }}</h5>
                <a href="{{ route('dashboard') }}" class="matte-btn d-inline-flex align-items-center gap-2 mt-3 text-decoration-none">
                    <i class="fas fa-arrow-left"></i>
                    <span data-i18n="backToMain">{{ __('Back to Main Dashboard') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
