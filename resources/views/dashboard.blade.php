@extends('layouts.dashboard')

@section('title', __('messages.dashboard'))
@section('page-title', __('messages.dashboard'))

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
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
            <h1 style="word-break: keep-all; text-wrap: balance; line-height: 1.4;"><span class="d-inline-block" style="white-space: nowrap;"><span data-i18n="welcomeBack">{{ __('Welcome Back,') }}</span> <span class="gradient-text">
                @if(auth()->user()->role === 'admin')
                    {{ __('messages.role_admin') }}
                @elseif(auth()->user()->role === 'receptionist')
                    {{ __('messages.role_receptionist') }}
                @elseif(auth()->user()->role === 'nurse')
                    {{ __('messages.role_nurse') }}
                @else
                    {{ __('messages.role_doctor') }} {{ auth()->user()->name }}
                @endif
            </span></span></h1>
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
            <a href="{{ route('appointments.index') }}" class="btn btn-davinci-primary d-inline-flex align-items-center gap-2">
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
                            'checked_in' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $statusIcons = [
                            'pending' => 'fa-clock',
                            'confirmed' => 'fa-check-circle',
                            'checked_in' => 'fa-check-circle',
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
                                <span class="activity-time">{{ \Carbon\Carbon::parse($appt->date)->format('M d') }} â€¢ {{ \Carbon\Carbon::parse($appt->time)->format('H:i') }}</span>
                            </div>
                            <div class="activity-details">
                                <span class="activity-doctor">
                                    <i class="fas fa-user-md me-1"></i>{{ $appt->doctor->name ?? __('Unknown') }}
                                </span>
                                @php $statusLabel = $appt->status === 'checked_in' ? __('messages.checked_in') : ucfirst($appt->status); @endphp
                                <span class="badge bg-{{ $color }}-subtle text-{{ $color }}" data-i18n="{{ strtolower($appt->status) }}">{{ $statusLabel }}</span>
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

const cc = Utils.getChartColors();

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
                            let currencySymbol = "{{ \App\Models\Setting::get('currency_symbol', 'ط±.ظٹ') }}";
                            return ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString() + ' ' + currencySymbol;
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

