@extends('layouts.dashboard')

@section('title', 'Reports Dashboard')
@section('page-title', 'Reports & Analytics')

@section('content')
<div class="row g-4 mb-4">
    <!-- Quick Stats Cards -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="icon-shape bg-success-subtle text-success rounded me-3">
                        <i class="fas fa-dollar-sign fa-lg"></i>
                    </div>
                    <span class="text-muted small text-uppercase fw-bold" data-i18n="revenueToday">Today's Revenue</span>
                </div>
                <h3 class="mb-0 fw-bold">${{ number_format($stats['today_revenue'], 2) }}</h3>
                <small class="text-success">
                    <i class="fas fa-calendar-day me-1"></i> {{ date('M d') }}
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="icon-shape bg-primary-subtle text-primary rounded me-3">
                        <i class="fas fa-chart-line fa-lg"></i>
                    </div>
                    <span class="text-muted small text-uppercase fw-bold" data-i18n="revenueMonth">Month Revenue</span>
                </div>
                <h3 class="mb-0 fw-bold">${{ number_format($stats['month_revenue'], 2) }}</h3>
                <small class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i> {{ date('F Y') }}
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="icon-shape bg-info-subtle text-info rounded me-3">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <span class="text-muted small text-uppercase fw-bold" data-i18n="totalPatients">Total Patients</span>
                </div>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['total_patients']) }}</h3>
                <small class="text-success">
                    <i class="fas fa-arrow-up me-1"></i> {{ $stats['new_patients_month'] }} <span data-i18n="newThisMonth">new this month</span>
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="icon-shape bg-danger-subtle text-danger rounded me-3">
                        <i class="fas fa-file-invoice-dollar fa-lg"></i>
                    </div>
                    <span class="text-muted small text-uppercase fw-bold" data-i18n="outstanding">Outstanding</span>
                </div>
                <h3 class="mb-0 fw-bold">${{ number_format($stats['outstanding_amount'], 2) }}</h3>
                <small class="text-danger">
                    {{ $stats['outstanding_invoices'] }} <span data-i18n="invoicesOverdue">invoices overdue/sent</span>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter Section (Visual Only for Dashboard, Functional in Individual Reports) -->
<div class="d-flex justify-content-end mb-4">
    <div class="btn-group">
        <button type="button" class="btn btn-white border active" data-i18n="thisWeek">This Week</button>
        <button type="button" class="btn btn-white border" data-i18n="thisMonth">This Month</button>
        <button type="button" class="btn btn-white border" data-i18n="thisYear">This Year</button>
    </div>
</div>

<div class="row">
    <!-- Financial Reports -->
    <div class="col-12 mb-4">
        <h5 class="mb-3 text-muted fw-bold text-uppercase small ls-1" data-i18n="financialReports">Financial Reports</h5>
        <div class="row g-3">
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-primary display-6">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h6 class="fw-bold" data-i18n="revenueReport">Revenue Report</h6>
                        <p class="text-muted small mb-3" data-i18n="revenueReportDesc">Detailed breakdown of income over time.</p>
                        <a href="{{ route('reports.revenue') }}" class="btn btn-sm btn-outline-primary stretched-link" data-i18n="viewReport">View Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-success display-6">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h6 class="fw-bold" data-i18n="incomeByDoctor">Income by Doctor</h6>
                        <p class="text-muted small mb-3" data-i18n="incomeByDoctorDesc">Earnings performance per practitioner.</p>
                        <a href="{{ route('reports.revenue.doctor') }}" class="btn btn-sm btn-outline-success stretched-link" data-i18n="viewReport">View Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-info display-6">
                            <i class="fas fa-briefcase-medical"></i>
                        </div>
                        <h6 class="fw-bold" data-i18n="salesByService">Sales by Service</h6>
                        <p class="text-muted small mb-3" data-i18n="salesByServiceDesc">Top performing clinic services.</p>
                        <a href="{{ route('reports.revenue.service') }}" class="btn btn-sm btn-outline-info stretched-link" data-i18n="viewReport">View Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-danger display-6">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h6 class="fw-bold" data-i18n="outstandingReport">Outstanding</h6>
                        <p class="text-muted small mb-3" data-i18n="outstandingReportDesc">Unpaid invoices and debts.</p>
                        <a href="{{ route('reports.outstanding') }}" class="btn btn-sm btn-outline-danger stretched-link" data-i18n="viewReport">View Report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operational Reports -->
    <div class="col-12 mb-4">
        <h5 class="mb-3 text-muted fw-bold text-uppercase small ls-1" data-i18n="operationalReports">Operational Reports</h5>
        <div class="row g-3">
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-secondary display-6">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="fw-bold" data-i18n="patientDemographics">Patient Demographics</h6>
                        <p class="text-muted small mb-3" data-i18n="patientDemographicsDesc">Age, gender, and registration trends.</p>
                        <a href="{{ route('reports.patients') }}" class="btn btn-sm btn-outline-secondary stretched-link" data-i18n="viewReport">View Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-warning display-6">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h6 class="fw-bold" data-i18n="appointmentsReport">Appointments</h6>
                        <p class="text-muted small mb-3" data-i18n="appointmentsReportDesc">Status summaries and no-show rates.</p>
                        <a href="{{ route('reports.appointments') }}" class="btn btn-sm btn-outline-warning stretched-link" data-i18n="viewReport">View Report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-shape {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hover-card {
    transition: transform 0.2s;
}
.hover-card:hover {
    transform: translateY(-5px);
}
</style>
@endsection
