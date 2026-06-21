@extends('layouts.dashboard')

@section('title', __('messages.revenue_report'))
@section('page-title', __('messages.revenue_report'))
@section('page-i18n', 'revenue_report')

@section('content')
<style>
    .form-control-luxury {
        padding: 0.6rem 1rem !important;
        border-radius: 10px !important;
        background-color: rgba(15, 61, 62, 0.02) !important;
        border: 1px solid rgba(15, 61, 62, 0.12) !important;
        color: var(--secondary) !important;
        font-weight: 500 !important;
        font-size: 0.9rem !important;
        transition: all 0.2s ease !important;
    }
    .form-control-luxury:focus {
        border-color: var(--secondary) !important;
        background-color: var(--white) !important;
        box-shadow: 0 0 0 3px rgba(15, 61, 62, 0.05) !important;
    }
    .metric-number-total {
        color: #2e7d32 !important;
        font-weight: 700 !important;
    }
    .metric-number-average {
        color: #2e5b5c !important;
        font-weight: 700 !important;
    }
    .metric-number-pending {
        color: #a94442 !important;
        font-weight: 700 !important;
    }
    .luxury-actions-container {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        margin-bottom: 1.5rem !important;
    }
    .luxury-actions-container .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 42px !important;
        border-radius: 10px !important;
        font-weight: 500 !important;
    }
    .luxury-filter-row {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }
    .luxury-date-inputs-wrapper {
        display: flex !important;
        gap: 12px !important;
    }
    .luxury-date-field {
        width: 150px !important;
    }
    .luxury-metrics-grid-row {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 16px !important;
        width: 100% !important;
        margin-bottom: 1.5rem !important;
    }
    .luxury-metric-col {
        flex: 1 !important;
        min-width: 0 !important;
    }
    .luxury-report-card {
        background: var(--white) !important;
        border-radius: 16px !important;
        padding: 1.5rem !important;
        height: 100% !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
        border: none !important;
        text-align: center !important;
    }
</style>

<div class="luxury-actions-container">
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary gap-2">
        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs"></i>
        <span class="text-sm fw-medium">{{ __('messages.backToReports') }}</span>
    </a>

    <div class="d-flex align-items-center gap-2">
        <button onclick="window.print()" class="btn btn-light border" style="color: var(--secondary); background: var(--white);"><i class="fas fa-print ml-2"></i>{{ __('messages.print') }}</button>
        <a href="{{ route('reports.export.excel', ['report' => 'revenue', 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" class="btn btn-light border" style="color: var(--secondary); background: var(--white);"><i class="fas fa-file-excel ml-2"></i>{{ __('messages.exportExcel') }}</a>
    </div>
</div>

<form action="{{ route('reports.revenue') }}" method="GET" id="revenueFilterForm" class="w-100">
    <input type="hidden" name="quick_filter" id="quickFilterInput" value="{{ request('quick_filter') }}">

    <div class="card border-0 p-4 mb-4 shadow-sm" style="background: var(--white); border-radius: 16px;">
        <div class="luxury-filter-row">
            <div class="d-flex flex-wrap gap-2">
                <button type="button" onclick="applyQuickFilter('today')" class="btn btn-sm {{ request('quick_filter') == 'today' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'today' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.today') }}</button>
                <button type="button" onclick="applyQuickFilter('this_week')" class="btn btn-sm {{ request('quick_filter') == 'this_week' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'this_week' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisWeek') }}</button>
                <button type="button" onclick="applyQuickFilter('this_month')" class="btn btn-sm {{ (request('quick_filter') == 'this_month' || !request('quick_filter')) ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ (request('quick_filter') == 'this_month' || !request('quick_filter')) ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisMonth') }}</button>
                <button type="button" onclick="applyQuickFilter('this_year')" class="btn btn-sm {{ request('quick_filter') == 'this_year' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'this_year' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisYear') }}</button>
            </div>

            <div class="luxury-date-inputs-wrapper">
                <div class="luxury-date-field">
                    <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.fromDate') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="document.getElementById('quickFilterInput').value=''; document.getElementById('revenueFilterForm').submit();" class="form-control form-control-luxury">
                </div>
                <div class="luxury-date-field">
                    <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.toDate') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" onchange="document.getElementById('quickFilterInput').value=''; document.getElementById('revenueFilterForm').submit();" class="form-control form-control-luxury">
                </div>
            </div>
        </div>
    </div>
</form>

<div class="luxury-metrics-grid-row">
    <div class="luxury-metric-col">
        <div class="luxury-report-card">
            <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.total_revenue') }}</span>
            <h3 class="metric-number-total mb-1"><span class="text-nowrap">{{ $currencySymbol }}{{ number_format($total_revenue, 2) }}</span></h3>
            <small class="text-muted text-xs d-block">{{ __('messages.collected') }}</small>
        </div>
    </div>

    <div class="luxury-metric-col">
        <div class="luxury-report-card">
            <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.total_invoices') }}</span>
            <h3 class="text-dark fw-bold mb-1" style="font-weight: 700; font-size: 1.6rem;">{{ $total_invoices }}</h3>
            <small class="text-muted text-xs d-block">{{ __('messages.generated') }}</small>
        </div>
    </div>

    <div class="luxury-metric-col">
        <div class="luxury-report-card">
            <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.pending_amount') }}</span>
            <h3 class="metric-number-pending mb-1"><span class="text-nowrap">{{ $currencySymbol }}{{ number_format($pending_amount, 2) }}</span></h3>
            <small class="text-muted text-xs d-block">{{ __('messages.unpaid_balances') }}</small>
        </div>
    </div>

    <div class="luxury-metric-col">
        <div class="luxury-report-card">
            <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.avg_invoice') }}</span>
            <h3 class="metric-number-average mb-1"><span class="text-nowrap">{{ $currencySymbol }}{{ number_format($avg_invoice, 2) }}</span></h3>
            <small class="text-muted text-xs d-block">{{ __('messages.per_patient') }}</small>
        </div>
    </div>
</div>

<script>
function applyQuickFilter(filterValue) {
    document.getElementById('quickFilterInput').value = filterValue;
    document.getElementsByName('date_from')[0].value = '';
    document.getElementsByName('date_to')[0].value = '';
    document.getElementById('revenueFilterForm').submit();
}
</script>

<div class="row">
    <!-- Chart Section -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">{{ __('messages.revenueTrend') }}</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">{{ __('messages.paymentMethods') }}</h6>
            </div>
            <div class="card-body">
                @if($revenue_by_method->count() > 0)
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="methodChart"></canvas>
                </div>
                <div class="mt-4">
                    <ul class="list-group list-group-flush small">
                        @foreach($revenue_by_method as $method => $amount)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-capitalize">{{ str_replace('_', ' ', $method) }}</span>
                            <span class="fw-bold text-nowrap">{{ $currencySymbol }}{{ number_format($amount, 2) }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @else
                <div class="text-center text-muted py-5">{{ __('messages.noPaymentsData') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Detailed Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">{{ __('messages.transactionDetails') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">{{ __('messages.dateColumn') }}</th>
                                <th>{{ __('messages.invoiceColumn') }}</th>
                                <th>{{ __('messages.patientColumn') }}</th>
                                <th>{{ __('messages.payment_method') }}</th>
                                <th>{{ __('messages.receiver') }}</th>
                                <th class="text-end pe-4">{{ __('messages.amountLabel') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td class="ps-4">{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-decoration-none">
                                        {{ $payment->invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle text-primary d-flex align-items-center justify-content-center me-2">
                                            {{ substr($payment->invoice->patient->name, 0, 1) }}
                                        </div>
                                        {{ $payment->invoice->patient->name }}
                                    </div>
                                </td>
                                <td><span class="badge text-dark border">{{ ucfirst($payment->payment_method) }}</span></td>
                                <td>{{ $payment->receiver->name ?? __('messages.systemLabel') }}</td>
                                <td class="text-end pe-4 fw-bold text-nowrap">{{ $currencySymbol }}{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">{{ __('messages.noTransactions') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>
<script>
    const isRtl = document.documentElement.dir === 'rtl';
    const chartFont = isRtl ? 'Tajawal' : 'Plus Jakarta Sans';
    const cc = Utils.getChartColors();

    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chart_labels),
                datasets: [{
                    label: '{{ __("messages.dailyRevenue") }}',
                    data: @json($chart_data),
                    borderColor: cc.lineColor,
                    backgroundColor: cc.fillGradient,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: cc.tooltipBg,
                        titleColor: cc.tooltipText,
                        bodyColor: cc.tooltipText,
                        cornerRadius: 8,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: cc.grid, borderDash: [2] },
                        ticks: { color: cc.tick, font: { family: chartFont } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: chartFont } }
                    }
                }
            }
        });
    }

    const ctx2 = document.getElementById('methodChart');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: @json($revenue_by_method->keys()),
                datasets: [{
                    data: @json($revenue_by_method->values()),
                    backgroundColor: cc.doughnutColors,
                    borderWidth: 2,
                    borderColor: cc.tooltipBg
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: isRtl ? 'left' : 'right',
                        labels: { font: { family: chartFont } }
                    }
                }
            }
        });
    }
</script>
@endsection
