@extends('layouts.dashboard')

@section('title', __('messages.dashboard'))
@section('page-title', __('messages.dashboard'))

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-content">
        <div class="welcome-text">
            <span class="welcome-badge" id="greeting-badge">
                <i class="fas fa-sun" id="greeting-icon"></i>
                <span id="dashboard-greeting" data-i18n="goodMorning">{{ __('Good Morning') }}</span>
            </span>
            <h1><span data-i18n="welcomeBack">{{ __('Welcome Back,') }}</span> <span class="gradient-text" data-i18n="doctor">{{ __('Doctor') }}</span></h1>
            <p data-i18n="dashboardSubtitle">{{ __("Here's what's happening with your clinic today") }}</p>
        </div>
        <div class="welcome-illustration">
            <div class="floating-card card-1">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="floating-card card-2">
                <i class="fas fa-stethoscope"></i>
            </div>
            <div class="floating-card card-3">
                <i class="fas fa-pills"></i>
            </div>
        </div>
    </div>
    <div class="welcome-wave"></div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    @if(auth()->user()->role === 'doctor')
        <!-- DOCTOR STATS -->
        <div class="stat-card-premium today-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $todayAppointments }}</span>
                <span class="stat-label" data-i18n="appointmentsToday">{{ __('Appointments Today') }}</span>
            </div>
            <div class="stat-decoration"></div>
        </div>

        <div class="stat-card-premium waiting-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $waitingPatients }}</span>
                <span class="stat-label" data-i18n="waitingRoom">{{ __('Waiting Room') }}</span>
            </div>
            <div class="stat-decoration"></div>
        </div>

        <div class="stat-card-premium appointments-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $weekAppointments }}</span>
                <span class="stat-label" data-i18n="thisWeek">{{ __('This Week') }}</span>
            </div>
            <div class="stat-decoration"></div>
        </div>

        <div class="stat-card-premium patients-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $monthAppointments }}</span>
                <span class="stat-label" data-i18n="thisMonth">{{ __('This Month') }}</span>
            </div>
            <div class="stat-decoration"></div>
        </div>
    @elseif(auth()->user()->hasRole('receptionist'))
        <!-- RECEPTIONIST DASHBOARD -->
        <div class="col-12 mb-4">
            @if($readyToBillCount > 0)
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="fas fa-file-invoice-dollar fa-2x me-3"></i>
                <div class="flex-grow-1">
<h5 class="alert-heading mb-1" data-i18n="billingActionRequired">{{ __('Billing Action Required') }}</h5>
                                    <p class="mb-0"><span data-i18n="thereAre">{{ __('There are') }}</span> <strong>{{ $readyToBillCount }}</strong> <span data-i18n="completedAppointmentsReady">{{ __('completed appointments ready for invoicing.') }}</span></p>
                </div>
                <a href="{{ route('appointments.index', ['status' => 'completed']) }}" class="btn btn-warning text-dark fw-bold" data-i18n="goToBilling">
                    {{ __('Go to Billing') }} <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            @else
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
<h5 class="alert-heading mb-1" data-i18n="allCaughtUp">{{ __('All Caught Up!') }}</h5>
                                     <p class="mb-0" data-i18n="noPendingBilling">{{ __('No pending billing actions required at this time.') }}</p>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Standard Appointment Stats -->
        <div class="stat-card-premium today-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $todayAppointments }}</span>
                    <span class="stat-label" data-i18n="todayAppts">{{ __("Today's Appointments") }}</span>
            </div>
        </div>
    @elseif(auth()->user()->hasRole('nurse'))
        <!-- NURSE DASHBOARD -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-nurse me-2"></i><span data-i18n="triageQueue">{{ __('Triage Queue (To Vitals)') }}</span></h5>
                    <span class="badge   text-primary">{{ $triageQueue->count() }} <span data-i18n="pending">{{ __('Pending') }}</span></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th data-i18n="timeTable">{{ __('Time') }}</th>
                                    <th data-i18n="patientName">{{ __('Patient Name') }}</th>
                                    <th data-i18n="doctor">{{ __('Doctor') }}</th>
                                    <th data-i18n="status">{{ __('Status') }}</th>
                                    <th data-i18n="action">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($triageQueue as $appt)
                                <tr>
                                    <td>{{ $appt->time->format('H:i') }}</td>
                                    <td class="fw-bold">{{ $appt->patient->name }}</td>
                                    <td>{{ $appt->doctor->name }}</td>
                                    <td><span class="badge bg-info" data-i18n="confirmed">{{ __('Confirmed') }}</span></td>
                                    <td>
                                        <a href="{{ route('nurse.vitals.create', $appt->id) }}" class="btn btn-success btn-sm text-white" data-i18n="recordVitals">
                                            <i class="fas fa-heartbeat me-1"></i> {{ __('Record Vitals') }}
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted" data-i18n="noPatientsInTriage">{{ __('Thinking... No patients in triage queue.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" data-i18n="waitingRoomReady"><i class="fas fa-chair me-2"></i>{{ __('Waiting Room (Ready for Doctor)') }}</h5>
                    <span class="badge bg-dark text-white" data-i18n="waiting">{{ $waitingList->count() }} {{ __('Waiting') }}</span>
                </div>
                <div class="card-body p-0">
                     <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th data-i18n="timeTable">{{ __('Time') }}</th>
                                    <th data-i18n="patientName">{{ __('Patient Name') }}</th>
                                    <th data-i18n="doctor">{{ __('Doctor') }}</th>
                                    <th data-i18n="status">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($waitingList as $appt)
                                <tr>
                                    <td>{{ $appt->time->format('H:i') }}</td>
                                    <td class="fw-bold">{{ $appt->patient->name }}</td>
                                    <td>{{ $appt->doctor->name }}</td>
                                    <td><span class="badge bg-warning text-dark" data-i18n="waiting">{{ __('Waiting') }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted" data-i18n="noPatientsWaiting">{{ __('No patients waiting.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="stat-card-premium patients-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $totalActivePatients }}</span>
<span class="stat-label" data-i18n="totalPatients">{{ __('Total Patients') }}</span>
                                <span class="stat-badge" data-i18n="newLabel">+{{ $newPatientsMonth }} {{ __('new') }}</span>
            </div>
            <div class="stat-decoration"></div>
        </div>

        <div class="stat-card-premium revenue-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ number_format($todayRevenue, 0) }}</span>
                <span class="stat-label" data-i18n="revenueToday">{{ __("Today's Revenue") }}</span>
            </div>
            <div class="stat-decoration"></div>
        </div>

        <div class="stat-card-premium appointments-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $todayAppointments }}</span>
                <span class="stat-label" data-i18n="todayAppts">{{ __("Today's Appts") }}</span>
            </div>
            <div class="stat-decoration"></div>
        </div>

        <div class="stat-card-premium invoice-card">
            <div class="stat-background"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon-bg"></div>
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $pendingInvoicesCount }}</span>
<span class="stat-label" data-i18n="pendingInvoices">{{ __('Pending Invoices') }}</span>
                                <small class="text-white-50"><span data-i18n="outstanding">{{ $pendingInvoicesAmount }}</span> <span data-i18n="pending">{{ __('pending') }}</span></small>
            </div>
            <div class="stat-decoration"></div>
        </div>
    @endif
</div>

<!-- Charts Section -->
<div class="charts-section">
    <!-- Status Overview -->
    <div class="chart-panel status-panel">
        <div class="panel-header">
            <div class="panel-title">
                <div class="title-icon"><i class="fas fa-chart-pie"></i></div>
                <div>
                    <h3 data-i18n="statusOverview">{{ __('Status Overview') }}</h3>
                    <p data-i18n="appointmentDistribution">{{ __('Appointment distribution') }}</p>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="donut-wrapper">
                <canvas id="statusChart"></canvas>
                <div class="donut-center">
                    <span class="donut-total">{{ $totalAppointments }}</span>
                    <span class="donut-label" data-i18n="total">{{ __('Total') }}</span>
                </div>
            </div>
            <div class="status-grid" id="statusGrid">
                @foreach(['pending', 'confirmed', 'completed', 'cancelled'] as $s)
                @php $count = $statusBreakdown[$s] ?? 0; @endphp
                <div class="status-item {{ $s }}">
                    <div class="status-indicator"></div>
                    <span class="status-name" data-i18n="{{ $s }}">{{ ucfirst($s) }}</span>
                    <span class="status-count">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="chart-panel trend-panel">
        <div class="panel-header">
            <div class="panel-title">
                <div class="title-icon"><i class="fas fa-chart-line"></i></div>
                <div>
                    <h3 data-i18n="monthlyRevenue">{{ __('Monthly Revenue') }}</h3>
                    <p data-i18n="revenueTrend">{{ __('Revenue trend over last 6 months') }}</p>
                </div>
            </div>
            <div class="panel-badge">
                <i class="fas fa-dollar-sign"></i>
                <span data-i18n="thisMonth">{{ $monthlyRevenue['revenue'][array_key_last($monthlyRevenue['revenue']) ?? 0] ?? 0 }} {{ __('this month') }}</span>
            </div>
        </div>
        <div class="panel-body">
            <div class="trend-chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="activity-section">
    <div class="chart-panel activity-panel">
        <div class="panel-header">
            <div class="panel-title">
                <div class="title-icon pulse"><i class="fas fa-bolt"></i></div>
                <div>
                    <h3 data-i18n="recentActivity">{{ __('Recent Activity') }}</h3>
                    <p data-i18n="latestAppointments">{{ __('Latest appointments in your clinic') }}</p>
                </div>
            </div>
            <a href="{{ route('appointments.index') }}" class="panel-action">
                <span data-i18n="viewAll">{{ __('View All') }}</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="panel-body">
            <div class="activity-list">
                @forelse($recentAppointments as $appt)
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'confirmed' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $statusIcons = [
                            'pending' => 'fa-clock',
                            'confirmed' => 'fa-check-circle',
                            'completed' => 'fa-check-double',
                            'cancelled' => 'fa-times-circle'
                        ];
                        $color = $statusColors[$appt->status] ?? 'secondary';
                        $icon = $statusIcons[$appt->status] ?? 'fa-calendar';
                    @endphp
                    <a href="{{ route('appointments.show', ['appointment' => $appt, 'from' => 'dashboard']) }}" class="activity-item text-decoration-none">
                        <div class="activity-icon bg-{{ $color }}-subtle text-{{ $color }}">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <span class="activity-title">{{ $appt->patient->name ?? __('Unknown') }}</span>
                                <span class="activity-time">{{ \Carbon\Carbon::parse($appt->date)->format('M d') }} • {{ \Carbon\Carbon::parse($appt->time)->format('H:i') }}</span>
                            </div>
                            <div class="activity-details">
                                <span class="activity-doctor">
                                    <i class="fas fa-user-md me-1"></i>{{ $appt->doctor->name ?? __('Unknown') }}
                                </span>
                                <span class="badge bg-{{ $color }}-subtle text-{{ $color }}" data-i18n="{{ strtolower($appt->status) }}">{{ ucfirst($appt->status) }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="empty-activity text-center py-4">
                        <i class="fas fa-calendar-day text-muted mb-3" style="font-size: 2.5rem;"></i>
                        <p class="text-muted mb-0" data-i18n="noAppointments">{{ __('No recent appointments') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/chartjs/chart.min.js') }}?v={{ filemtime(public_path('vendor/chartjs/chart.min.js')) }}"></script>
<script>
// Chart data from server
const statusLabels = ['pending', 'confirmed', 'completed', 'cancelled'];
const statusDataRaw = @json($statusBreakdown);
const statusData = statusLabels.map(s => statusDataRaw[s] || 0);

const monthlyRevenue = {
    labels: [@foreach($monthlyRevenue['labels'] as $l)'{{ $l }}'@if(!$loop->last),@endif @endforeach],
    data: [@foreach($monthlyRevenue['revenue'] as $r){{ $r }}@if(!$loop->last),@endif @endforeach]
};

const t = (key) => {
    if (typeof window.translations !== 'undefined') {
        const lang = document.documentElement.lang || 'en';
        if (window.translations[lang] && window.translations[lang][key]) return window.translations[lang][key];
    }
    return key.charAt(0).toUpperCase() + key.slice(1);
};

const translateMonthLabel = (label) => {
    const lang = document.documentElement.lang || 'en';
    if (lang !== 'ar' || !window.translations) return label;
    const parts = label.split(' ');
    if (parts.length === 2) {
        const monthMap = { jan:'month_jan',feb:'month_feb',mar:'month_mar',apr:'month_apr',may:'month_may',jun:'month_jun',jul:'month_jul',aug:'month_aug',sep:'month_sep',oct:'month_oct',nov:'month_nov',dec:'month_dec' };
        const key = monthMap[parts[0].toLowerCase()];
        if (key && window.translations.ar[key]) {
            return window.translations.ar[key] + ' ' + parts[1];
        }
    }
    return label;
};

function getChartColors() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const s = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();
    return {
        grid: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
        tick: isDark ? s('--text-secondary') : '#888888',
        border: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
        tooltipBg: isDark ? s('--white') : '#ffffff',
        tooltipText: isDark ? s('--text-primary') : '#2c2c2c',
        tooltipBorder: isDark ? 'rgba(42,168,138,0.2)' : 'rgba(0,0,0,0.1)',
        pointBg: isDark ? s('--secondary') : '#0f3d3e',
        pointBorder: isDark ? s('--body-bg') : '#ffffff',
        fillGradient: isDark ? 'rgba(42,168,138,0.08)' : 'rgba(15,61,62,0.08)',
        lineColor: isDark ? s('--secondary') : '#0f3d3e',
        pending: isDark ? '#f0ad4e' : 'rgba(191,140,48,0.85)',
        confirmed: isDark ? '#2ecc71' : 'rgba(46,93,52,0.85)',
        completed: isDark ? '#5dade2' : 'rgba(61,90,128,0.85)',
        cancelled: isDark ? '#e74c3c' : 'rgba(139,58,58,0.85)',
        chartBar: isDark ? s('--secondary') : '#2dd4bf',
        chartBarHover: isDark ? '#34c4a3' : '#00f2fe',
    };
}

const cc = getChartColors();

// Status Chart (doughnut)
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(t),
            datasets: [{
                data: statusData,
                backgroundColor: [cc.pending, cc.confirmed, cc.completed, cc.cancelled],
                borderWidth: 2,
                borderColor: cc.pointBorder,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: cc.tooltipBg,
                    titleColor: cc.tooltipText,
                    bodyColor: cc.tooltipText,
                    borderColor: cc.tooltipBorder,
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                            return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Monthly Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    const lang = document.documentElement.lang || 'en';
    const revenueLabels = monthlyRevenue.labels.map(translateMonthLabel);
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: t('revenue'),
                data: monthlyRevenue.data,
                fill: true,
                backgroundColor: cc.fillGradient,
                borderColor: cc.lineColor,
                borderWidth: 3,
                tension: 0.4,
                pointBackgroundColor: cc.pointBg,
                pointBorderColor: cc.pointBorder,
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: cc.tooltipBg,
                    titleColor: cc.tooltipText,
                    bodyColor: cc.tooltipText,
                    borderColor: cc.tooltipBorder,
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    callbacks: {
                        label: function(ctx) {
                            return t('revenue') + ': $' + ctx.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: cc.tick, font: { size: 11, family: lang === 'ar' ? 'Tajawal' : undefined } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: cc.grid },
                    ticks: {
                        color: cc.tick,
                        font: { size: 11 },
                        callback: function(value) { return '$' + value.toLocaleString(); }
                    }
                }
            }
        }
    });
}

// On theme toggle, reload to reinitialize charts with correct colors
// (theme persistence handled by app.js)
</script>
@endsection
