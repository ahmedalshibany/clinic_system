@extends('layouts.dashboard')

@section('title', __('messages.reports'))
@section('page-title', __('messages.reports'))
@section('page-i18n', 'reports')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/reports.css') }}?v={{ filemtime(public_path('css/reports.css')) }}">
<style>
    .luxury-kpi-card-shell {
        background: var(--white) !important;
        border-radius: var(--radius-lg, 16px) !important;
        box-shadow: var(--shadow-soft, 0 4px 20px rgba(0, 0, 0, 0.02)) !important;
        padding: 1.5rem !important;
        min-height: 115px !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        position: relative !important;
        overflow: hidden !important;
        transition: transform var(--duration-base) var(--ease-out),
            box-shadow var(--duration-base) var(--ease-out) !important;
    }

    .luxury-kpi-card-shell::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 50% 0%, var(--secondary, #0f3d3e) 0%, transparent 70%);
        opacity: 0;
        border-radius: inherit;
        transition: opacity var(--duration-base) var(--ease-out);
        pointer-events: none;
    }

    .luxury-kpi-card-shell:hover {
        box-shadow: var(--shadow-hover, 0 12px 40px rgba(0, 0, 0, 0.1)) !important;
    }

    .luxury-kpi-card-shell:hover::before {
        opacity: 0.08;
    }

    .luxury-kpi-card-shell {
        animation: riseUp var(--duration-base) var(--ease-out) forwards;
        opacity: 0;
    }

    .row.g-4.mb-4 > .col-xl-3:nth-child(1) .luxury-kpi-card-shell { animation-delay: 50ms; }
    .row.g-4.mb-4 > .col-xl-3:nth-child(2) .luxury-kpi-card-shell { animation-delay: 100ms; }
    .row.g-4.mb-4 > .col-xl-3:nth-child(3) .luxury-kpi-card-shell { animation-delay: 150ms; }
    .row.g-4.mb-4 > .col-xl-3:nth-child(4) .luxury-kpi-card-shell { animation-delay: 200ms; }

    .kpi-text-stack {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        text-align: right !important;
        flex: 1 !important;
        padding-right: 0.25rem !important;
        white-space: nowrap !important;
    }

    .luxury-kpi-badge {
        width: 52px !important;
        height: 52px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        margin-right: 1rem !important;
    }

    aside, .main-sidebar, div[class*="sidebar"] { overflow-y: hidden !important; max-height: 100vh !important; }
    aside a, .sidebar-link, .nav-item a { padding-top: 0.65rem !important; padding-bottom: 0.65rem !important; margin-bottom: 0.25rem !important; }

    @media (prefers-reduced-motion: reduce) {
        .luxury-kpi-card-shell {
            animation: none !important;
            opacity: 1 !important;
        }
    }
</style>
@endsection

@section('content')


<div class="dashboard-content">

    {{-- ═══ KPI Crown Jewels ═══ --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="luxury-kpi-card-shell">
                <div class="kpi-text-stack">
                    <span class="text-muted text-xs mb-1" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.revenueToday') }}</span>
                    <h3 class="fw-bold mb-1" style="font-size: 1.85rem; color: var(--text-primary); margin: 0; line-height: 1.2;">
                        {{ number_format($stats['today_revenue'], 2) }} <span class="text-sm fw-normal text-muted">{{ $currencySymbol }}</span>
                    </h3>
                    <small class="text-muted mt-1"><i class="far fa-calendar-alt me-1"></i> {{ now()->format('M d') }}</small>
                </div>
                <div class="luxury-kpi-badge" style="background: rgba(15, 61, 62, 0.08); color: var(--secondary);">
                    <i class="fas fa-money-bill-wave" style="font-size: 1.4rem;"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="luxury-kpi-card-shell">
                <div class="kpi-text-stack">
                    <span class="text-muted text-xs mb-1" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.revenueMonth') }}</span>
                    <h3 class="fw-bold mb-1" style="font-size: 1.85rem; color: var(--text-primary); margin: 0; line-height: 1.2;">
                        {{ number_format($stats['month_revenue'], 2) }} <span class="text-sm fw-normal text-muted">{{ $currencySymbol }}</span>
                    </h3>
                    <small class="text-muted mt-1"><i class="far fa-calendar-alt me-1"></i> {{ now()->format('F Y') }}</small>
                </div>
                <div class="luxury-kpi-badge" style="background: rgba(15, 61, 62, 0.08); color: var(--secondary);">
                    <i class="fas fa-chart-line" style="font-size: 1.4rem;"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="luxury-kpi-card-shell">
                <div class="kpi-text-stack">
                    <span class="text-muted text-xs mb-1" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.totalPatients') }}</span>
                    <h3 class="fw-bold mb-1" style="font-size: 1.85rem; color: var(--text-primary); margin: 0; line-height: 1.2;">
                        {{ number_format($stats['total_patients']) }}
                    </h3>
                    <small class="text-success text-xs fw-semibold mt-1">
                        <i class="fas fa-arrow-up me-1"></i> {{ $stats['new_patients_month'] }} {{ __('messages.newThisMonth') }}
                    </small>
                </div>
                <div class="luxury-kpi-badge" style="background: rgba(15, 61, 62, 0.08); color: var(--secondary);">
                    <i class="fas fa-users" style="font-size: 1.4rem;"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="luxury-kpi-card-shell">
                <div class="kpi-text-stack">
                    <span class="text-muted text-xs mb-1" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.outstanding') }}</span>
                    <h3 class="fw-bold mb-1" style="font-size: 1.85rem; color: var(--text-primary); margin: 0; line-height: 1.2;">
                        {{ number_format($stats['outstanding_amount'], 2) }} <span class="text-sm fw-normal text-muted">{{ $currencySymbol }}</span>
                    </h3>
                    <small class="text-danger text-xs fw-semibold mt-1">
                        {{ $stats['outstanding_invoices'] }} {{ __('messages.invoicesOverdue') }}
                    </small>
                </div>
                <div class="luxury-kpi-badge" style="background: rgba(160, 82, 45, 0.09); color: var(--accent);">
                    <i class="fas fa-file-invoice-dollar" style="font-size: 1.4rem;"></i>
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
                <a href="{{ route('reports.revenue') }}" class="report-action-plate text-decoration-none">
                    <span class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            </div>

            <div class="reports-interactive-card report-card-doctor">
                <div class="report-card-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <h6>{{ __('messages.incomeByDoctor') }}</h6>
                <p>{{ __('messages.incomeByDoctorDesc') }}</p>
                <a href="{{ route('reports.revenue.doctor') }}" class="report-action-plate text-decoration-none">
                    <span class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            </div>

            <div class="reports-interactive-card report-card-service">
                <div class="report-card-icon">
                    <i class="fas fa-briefcase-medical"></i>
                </div>
                <h6>{{ __('messages.salesByService') }}</h6>
                <p>{{ __('messages.salesByServiceDesc') }}</p>
                <a href="{{ route('reports.revenue.service') }}" class="report-action-plate text-decoration-none">
                    <span class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            </div>

            <div class="reports-interactive-card report-card-outstanding">
                <div class="report-card-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h6>{{ __('messages.outstandingReport') }}</h6>
                <p>{{ __('messages.outstandingReportDesc') }}</p>
                <a href="{{ route('reports.outstanding') }}" class="report-action-plate text-decoration-none">
                    <span class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
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
                <a href="{{ route('reports.patients') }}" class="report-action-plate text-decoration-none">
                    <span class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            </div>

            <div class="reports-interactive-card report-card-appointments">
                <div class="report-card-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h6>{{ __('messages.appointmentsReport') }}</h6>
                <p>{{ __('messages.appointmentsReportDesc') }}</p>
                <a href="{{ route('reports.appointments') }}" class="report-action-plate text-decoration-none">
                    <span class="report-action-link">
                        {{ __('messages.viewReport') }}
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
