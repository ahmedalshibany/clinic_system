@extends('layouts.dashboard')

@section('title', __('messages.reports'))
@section('page-title', __('messages.reports'))

@section('styles')
<link rel="stylesheet" href="{{ asset('css/reports.css') }}?v={{ filemtime(public_path('css/reports.css')) }}">
<style>
    /* 1. ELIMINATE THE COLLISION — CLEAN FLEX LAYOUT */
    .kpi-luxury-card {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        padding: 1.5rem !important;
        min-height: 120px !important;
    }

    [dir="rtl"] .kpi-luxury-card {
        flex-direction: row !important;
    }

    .kpi-text-stack {
        position: static !important;
        transform: none !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        text-align: right !important;
        flex-grow: 1 !important;
    }

    [dir="ltr"] .kpi-text-stack {
        text-align: left !important;
    }

    .luxury-kpi-badge {
        position: static !important;
        transform: none !important;
        width: 52px !important;
        height: 52px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        margin-right: 1rem !important;
    }

    [dir="ltr"] .luxury-kpi-badge {
        margin-right: 0 !important;
        margin-left: 1rem !important;
    }

    .luxury-kpi-badge i {
        font-size: 1.45rem !important;
    }

    /* Retain Fixed Clean Sidebar Padding */
    aside, .main-sidebar, div[class*="sidebar"] {
        overflow-y: hidden !important;
        max-height: 100vh !important;
    }

    aside a, .sidebar-link, .nav-item a {
        padding-top: 0.65rem !important;
        padding-bottom: 0.65rem !important;
        margin-bottom: 0.25rem !important;
    }
</style>
@endsection

@section('content')


<div class="dashboard-content">

    {{-- ═══ KPI Crown Jewels ═══ --}}
    <div class="report-kpi-grid">
        {{-- Revenue Today --}}
        <div class="card p-3">
            <div class="kpi-luxury-card">
                <div class="kpi-text-stack">
                    <span class="text-muted text-xs mb-1" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.revenueToday') }}</span>
                    <h3 class="fw-bold mb-1" style="font-size: 2rem; color: var(--text-primary); margin: 0;">{{ number_format($stats['today_revenue'], 2) }} <small class="text-sm fw-normal">{{ __('messages.currencySymbol') }}</small></h3>
                    <small class="text-muted mt-1"><i class="far fa-calendar-alt me-1"></i> {{ now()->format('M d') }}</small>
                </div>
                <div class="luxury-kpi-badge" style="background: rgba(15, 61, 62, 0.08); color: var(--secondary);">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        {{-- Revenue Month --}}
        <div class="card p-3">
            <div class="kpi-luxury-card">
                <div class="kpi-text-stack">
                    <span class="text-muted text-xs mb-1" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.revenueMonth') }}</span>
                    <h3 class="fw-bold mb-1" style="font-size: 2rem; color: var(--text-primary); margin: 0;">{{ number_format($stats['month_revenue'], 2) }} <small class="text-sm fw-normal">{{ __('messages.currencySymbol') }}</small></h3>
                    <small class="text-muted mt-1"><i class="far fa-calendar-alt me-1"></i> {{ now()->format('F Y') }}</small>
                </div>
                <div class="luxury-kpi-badge" style="background: rgba(15, 61, 62, 0.08); color: var(--secondary);">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        {{-- Total Patients --}}
        <div class="card p-3">
            <div class="kpi-luxury-card">
                <div class="kpi-text-stack">
                    <span class="text-muted text-xs mb-1" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.totalPatients') }}</span>
                    <h3 class="fw-bold mb-1" style="font-size: 2rem; color: var(--text-primary); margin: 0;">{{ number_format($stats['total_patients']) }}</h3>
                    <small class="text-success text-xs fw-semibold mt-1"><i class="fas fa-arrow-up me-1"></i> {{ $stats['new_patients_month'] }} {{ __('messages.newThisMonth') }}</small>
                </div>
                <div class="luxury-kpi-badge" style="background: rgba(15, 61, 62, 0.08); color: var(--secondary);">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        {{-- Outstanding Invoices --}}
        <div class="card p-3">
            <div class="kpi-luxury-card">
                <div class="kpi-text-stack">
                    <span class="text-muted text-xs mb-1" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.outstanding') }}</span>
                    <h3 class="fw-bold mb-1" style="font-size: 2rem; color: var(--text-primary); margin: 0;">{{ number_format($stats['outstanding_amount'], 2) }} <small class="text-sm fw-normal">{{ __('messages.currencySymbol') }}</small></h3>
                    <small class="text-danger text-xs fw-semibold mt-1">{{ $stats['outstanding_invoices'] }} {{ __('messages.invoicesOverdue') }}</small>
                </div>
                <div class="luxury-kpi-badge" style="background: rgba(160, 82, 45, 0.09); color: var(--accent);">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Financial Reports ═══ --}}
    <div class="report-section">
        <div class="report-section-header">
            <h5>{{ __('messages.financialReports') }}</h5>
            <div class="report-section-line"></div>
        </div>

        <div class="report-card-grid">
            <div class="reports-interactive-card report-card-revenue">
                <div class="report-card-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h6>{{ __('messages.revenueReport') }}</h6>
                <p>{{ __('messages.revenueReportDesc') }}</p>
                <div class="report-action-plate">
                    <a href="{{ route('reports.revenue') }}" class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="reports-interactive-card report-card-doctor">
                <div class="report-card-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <h6>{{ __('messages.incomeByDoctor') }}</h6>
                <p>{{ __('messages.incomeByDoctorDesc') }}</p>
                <div class="report-action-plate">
                    <a href="{{ route('reports.revenue.doctor') }}" class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="reports-interactive-card report-card-service">
                <div class="report-card-icon">
                    <i class="fas fa-briefcase-medical"></i>
                </div>
                <h6>{{ __('messages.salesByService') }}</h6>
                <p>{{ __('messages.salesByServiceDesc') }}</p>
                <div class="report-action-plate">
                    <a href="{{ route('reports.revenue.service') }}" class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="reports-interactive-card report-card-outstanding">
                <div class="report-card-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h6>{{ __('messages.outstandingReport') }}</h6>
                <p>{{ __('messages.outstandingReportDesc') }}</p>
                <div class="report-action-plate">
                    <a href="{{ route('reports.outstanding') }}" class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Operational Reports ═══ --}}
    <div class="report-section">
        <div class="report-section-header">
            <h5>{{ __('messages.operationalReports') }}</h5>
            <div class="report-section-line"></div>
        </div>

        <div class="report-card-grid">
            <div class="reports-interactive-card report-card-patients">
                <div class="report-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h6>{{ __('messages.patientDemographics') }}</h6>
                <p>{{ __('messages.patientDemographicsDesc') }}</p>
                <div class="report-action-plate">
                    <a href="{{ route('reports.patients') }}" class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="reports-interactive-card report-card-appointments">
                <div class="report-card-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h6>{{ __('messages.appointmentsReport') }}</h6>
                <p>{{ __('messages.appointmentsReportDesc') }}</p>
                <div class="report-action-plate">
                    <a href="{{ route('reports.appointments') }}" class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
