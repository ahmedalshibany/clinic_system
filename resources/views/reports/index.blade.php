@extends('layouts.dashboard')

@section('title', __('messages.reports'))
@section('page-title', __('messages.reports'))

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
                    <span class="text-muted small text-uppercase fw-bold">{{ __('messages.revenueToday') }}</span>
                </div>
                <h3 class="mb-0 fw-bold">{{ __('messages.currencySymbol') }}{{ number_format($stats['today_revenue'], 2) }}</h3>
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
                    <span class="text-muted small text-uppercase fw-bold">{{ __('messages.revenueMonth') }}</span>
                </div>
                <h3 class="mb-0 fw-bold">{{ __('messages.currencySymbol') }}{{ number_format($stats['month_revenue'], 2) }}</h3>
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
                    <span class="text-muted small text-uppercase fw-bold">{{ __('messages.totalPatients') }}</span>
                </div>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['total_patients']) }}</h3>
                <small class="text-success">
                    <i class="fas fa-arrow-up me-1"></i> {{ $stats['new_patients_month'] }} {{ __('messages.newThisMonth') }}
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
                    <span class="text-muted small text-uppercase fw-bold">{{ __('messages.outstanding') }}</span>
                </div>
                <h3 class="mb-0 fw-bold">{{ __('messages.currencySymbol') }}{{ number_format($stats['outstanding_amount'], 2) }}</h3>
                <small class="text-danger">
                    {{ $stats['outstanding_invoices'] }} {{ __('messages.invoicesOverdue') }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter Section (Visual Only for Dashboard, Functional in Individual Reports) -->
<div class="d-flex justify-content-end mb-4">
    <div class="btn-group">
        <button type="button" class="btn btn-white border active">{{ __('messages.thisWeek') }}</button>
        <button type="button" class="btn btn-white border">{{ __('messages.thisMonth') }}</button>
        <button type="button" class="btn btn-white border">{{ __('messages.thisYear') }}</button>
    </div>
</div>

<div class="row">
    <!-- Financial Reports -->
    <div class="col-12 mb-4">
        <h5 class="mb-3 text-muted fw-bold text-uppercase small ls-1">{{ __('messages.financialReports') }}</h5>
        <div class="row g-3">
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-primary display-6">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h6 class="fw-bold">{{ __('messages.revenueReport') }}</h6>
                        <p class="text-muted small mb-3">{{ __('messages.revenueReportDesc') }}</p>
                        <a href="{{ route('reports.revenue') }}" class="btn btn-sm btn-outline-primary stretched-link">{{ __('messages.viewReport') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-success display-6">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h6 class="fw-bold">{{ __('messages.incomeByDoctor') }}</h6>
                        <p class="text-muted small mb-3">{{ __('messages.incomeByDoctorDesc') }}</p>
                        <a href="{{ route('reports.revenue.doctor') }}" class="btn btn-sm btn-outline-success stretched-link">{{ __('messages.viewReport') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-info display-6">
                            <i class="fas fa-briefcase-medical"></i>
                        </div>
                        <h6 class="fw-bold">{{ __('messages.salesByService') }}</h6>
                        <p class="text-muted small mb-3">{{ __('messages.salesByServiceDesc') }}</p>
                        <a href="{{ route('reports.revenue.service') }}" class="btn btn-sm btn-outline-info stretched-link">{{ __('messages.viewReport') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-danger display-6">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h6 class="fw-bold">{{ __('messages.outstandingReport') }}</h6>
                        <p class="text-muted small mb-3">{{ __('messages.outstandingReportDesc') }}</p>
                        <a href="{{ route('reports.outstanding') }}" class="btn btn-sm btn-outline-danger stretched-link">{{ __('messages.viewReport') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operational Reports -->
    <div class="col-12 mb-4">
        <h5 class="mb-3 text-muted fw-bold text-uppercase small ls-1">{{ __('messages.operationalReports') }}</h5>
        <div class="row g-3">
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-secondary display-6">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="fw-bold">{{ __('messages.patientDemographics') }}</h6>
                        <p class="text-muted small mb-3">{{ __('messages.patientDemographicsDesc') }}</p>
                        <a href="{{ route('reports.patients') }}" class="btn btn-sm btn-outline-secondary stretched-link">{{ __('messages.viewReport') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-warning display-6">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h6 class="fw-bold">{{ __('messages.appointmentsReport') }}</h6>
                        <p class="text-muted small mb-3">{{ __('messages.appointmentsReportDesc') }}</p>
                        <a href="{{ route('reports.appointments') }}" class="btn btn-sm btn-outline-warning stretched-link">{{ __('messages.viewReport') }}</a>
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
