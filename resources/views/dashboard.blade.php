@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-content">
        <div class="welcome-text">
            <span class="welcome-badge">
                <i class="fas fa-sun"></i>
                <span data-i18n="goodMorning">Good Morning</span>
            </span>
            <h1 data-i18n="welcomeBack">Welcome Back, <span class="gradient-text" data-i18n="doctor">Doctor</span></h1>
            <p data-i18n="dashboardSubtitle">Here's what's happening with your clinic today</p>
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
                <span class="stat-label">Appointments Today</span>
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
                <span class="stat-label">Waiting Room</span>
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
                <span class="stat-label">This Week</span>
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
                <span class="stat-label">This Month</span>
            </div>
            <div class="stat-decoration"></div>
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
                    <h5 class="alert-heading mb-1">Billing Action Required</h5>
                    <p class="mb-0">There are <strong>{{ $readyToBillCount }}</strong> completed appointments ready for invoicing.</p>
                </div>
                <a href="{{ route('appointments.index', ['status' => 'completed']) }}" class="btn btn-warning text-dark fw-bold">
                    Go to Billing <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            @else
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                     <h5 class="alert-heading mb-1">All Caught Up!</h5>
                     <p class="mb-0">No pending billing actions required at this time.</p>
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
                <span class="stat-label">Today's Appointments</span>
            </div>
        </div>
    @elseif(auth()->user()->hasRole('nurse'))
        <!-- NURSE DASHBOARD -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-nurse me-2"></i>Triage Queue (To Vitals)</h5>
                    <span class="badge bg-light text-primary">{{ $triageQueue->count() }} Pending</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Patient Name</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($triageQueue as $appt)
                                <tr>
                                    <td>{{ $appt->time->format('H:i') }}</td>
                                    <td class="fw-bold">{{ $appt->patient->name }}</td>
                                    <td>{{ $appt->doctor->name }}</td>
                                    <td><span class="badge bg-info">Confirmed</span></td>
                                    <td>
                                        <a href="{{ route('nurse.vitals.create', $appt->id) }}" class="btn btn-success btn-sm text-white">
                                            <i class="fas fa-heartbeat me-1"></i> Record Vitals
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Thinking... No patients in triage queue.</td>
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
                    <h5 class="mb-0"><i class="fas fa-chair me-2"></i>Waiting Room (Ready for Doctor)</h5>
                    <span class="badge bg-dark text-white">{{ $waitingList->count() }} Waiting</span>
                </div>
                <div class="card-body p-0">
                     <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Patient Name</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($waitingList as $appt)
                                <tr>
                                    <td>{{ $appt->time->format('H:i') }}</td>
                                    <td class="fw-bold">{{ $appt->patient->name }}</td>
                                    <td>{{ $appt->doctor->name }}</td>
                                    <td><span class="badge bg-warning text-dark">Waiting</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No patients waiting.</td>
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
                <span class="stat-number">{{ $totalPatients }}</span>
                <span class="stat-label" data-i18n="totalPatients">Total Patients</span>
                <span class="stat-badge">+{{ $newPatientsMonth }} new</span>
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
                <span class="stat-label" data-i18n="revenueToday">Today's Revenue</span>
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
                <span class="stat-label" data-i18n="todayAppts">Today's Appts</span>
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
                <span class="stat-label" data-i18n="pendingInvoices">Pending Invoices</span>
                <small class="text-white-50"><span data-i18n="outstanding">{{ $pendingInvoicesAmount }}</span> <span data-i18n="pending">pending</span></small>
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
                    <h3 data-i18n="statusOverview">Status Overview</h3>
                    <p data-i18n="appointmentDistribution">Appointment distribution</p>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="donut-wrapper">
                <canvas id="statusChart"></canvas>
                <div class="donut-center">
                    <span class="donut-total">{{ $totalAppointments }}</span>
                    <span class="donut-label" data-i18n="total">Total</span>
                </div>
            </div>
            <div class="status-grid">
                <div class="status-item pending">
                    <div class="status-indicator"></div>
                    <span class="status-name" data-i18n="pending">Pending</span>
                    <span class="status-count">{{ $pending }}</span>
                </div>
                <div class="status-item confirmed">
                    <div class="status-indicator"></div>
                    <span class="status-name" data-i18n="confirmed">Confirmed</span>
                    <span class="status-count">{{ $confirmed }}</span>
                </div>
                <div class="status-item completed">
                    <div class="status-indicator"></div>
                    <span class="status-name" data-i18n="completed">Completed</span>
                    <span class="status-count">{{ $completed }}</span>
                </div>
                <div class="status-item cancelled">
                    <div class="status-indicator"></div>
                    <span class="status-name" data-i18n="cancelled">Cancelled</span>
                    <span class="status-count">{{ $cancelled }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Trend -->
    <div class="chart-panel trend-panel">
        <div class="panel-header">
            <div class="panel-title">
                <div class="title-icon"><i class="fas fa-chart-line"></i></div>
                <div>
                    <h3 data-i18n="weeklyTrend">Weekly Trend</h3>
                    <p data-i18n="last7days">Last 7 days performance</p>
                </div>
            </div>
            <div class="panel-badge">
                <i class="fas fa-arrow-trend-up"></i>
                <span data-i18n="live">Live</span>
            </div>
        </div>
        <div class="panel-body">
            <div class="trend-chart-wrapper">
                <canvas id="weeklyChart"></canvas>
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
                    <h3 data-i18n="recentActivity">Recent Activity</h3>
                    <p data-i18n="latestAppointments">Latest appointments in your clinic</p>
                </div>
            </div>
            <a href="{{ route('appointments.index') }}" class="panel-action">
                <span data-i18n="viewAll">View All</span>
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
                    <div class="activity-item">
                        <div class="activity-icon bg-{{ $color }}-subtle text-{{ $color }}">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <span class="activity-title">{{ $appt->patient->name ?? 'Unknown' }}</span>
                                <span class="activity-time">{{ \Carbon\Carbon::parse($appt->date)->format('M d') }} • {{ \Carbon\Carbon::parse($appt->time)->format('H:i') }}</span>
                            </div>
                            <div class="activity-details">
                                    <i class="fas fa-user-md me-1"></i>{{ $appt->doctor->name ?? 'Unknown' }}
                                </span>
                                <span class="badge bg-{{ $color }}-subtle text-{{ $color }}" data-i18n="{{ strtolower($appt->status) }}">{{ ucfirst($appt->status) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-activity text-center py-4">
                        <i class="fas fa-calendar-day text-muted mb-3" style="font-size: 2.5rem;"></i>
                        <p class="text-muted mb-0" data-i18n="noAppointments">No recent appointments</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>
<script>
// Chart data from server
const statusData = [{{ $pending }}, {{ $confirmed }}, {{ $completed }}, {{ $cancelled }}];
const weeklyData = {
    labels: [@foreach($weeklyData as $day)'{{ $day['day'] }}'@if(!$loop->last),@endif @endforeach],
    data: [@foreach($weeklyData as $day){{ $day['count'] }}@if(!$loop->last),@endif @endforeach]
};

// Status Chart
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    const t = (key) => {
        if (typeof window.translations !== 'undefined') {
            const lang = document.documentElement.lang || 'en';
            if (window.translations[lang] && window.translations[lang][key]) {
                return window.translations[lang][key];
            }
        }
        return key.charAt(0).toUpperCase() + key.slice(1);
    };

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: [t('pending'), t('confirmed'), t('completed'), t('cancelled')],
            datasets: [{
                data: statusData,
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(13, 202, 240, 0.8)',
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderWidth: 2,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
}

// Weekly Chart
const weeklyCtx = document.getElementById('weeklyChart');
if (weeklyCtx) {
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: weeklyData.labels,
            datasets: [{
                label: 'Appointments',
                data: weeklyData.data,
                fill: true,
                backgroundColor: 'rgba(47, 65, 86, 0.1)',
                borderColor: 'rgba(47, 65, 86, 0.8)',
                borderWidth: 3,
                tension: 0.4,
                pointBackgroundColor: 'rgba(47, 65, 86, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}
</script>
@endsection
