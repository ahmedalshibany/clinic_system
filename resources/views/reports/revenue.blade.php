@extends('layouts.dashboard')

@section('title', __('messages.reports') . ' / ' . __('messages.totalRevenue'))
@section('page-title', __('messages.reports') . ' / ' . __('messages.totalRevenue'))

@section('content')
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mb-3">
    <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-1"></i> {{ __('messages.backToReports') }}
</a>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold m-0" style="color: var(--text-primary);">
        {{ __('messages.reports') }} / {{ __('messages.totalRevenue') }}
    </h4>
    <div class="d-flex align-items-center gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fas fa-print"></i> {{ __('messages.print') }}
        </button>
        <a href="{{ route('reports.export.excel', ['report' => 'revenue'] + request()->all()) }}" class="btn btn-success d-inline-flex align-items-center gap-2">
            <i class="fas fa-file-excel"></i> {{ __('messages.exportExcel') }}
        </a>
    </div>
</div>

<!-- Date Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small">{{ __('messages.fromDate') }}</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">{{ __('messages.toDate') }}</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small d-block">{{ __('messages.quickFilters') }}</label>
                <div class="btn-group w-100">
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('today')">{{ __('messages.today') }}</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('week')">{{ __('messages.thisWeek') }}</button>
                    <button type="button" class="btn btn-outline-primary active" onclick="setDateRange('month')">{{ __('messages.thisMonth') }}</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('year')">{{ __('messages.thisYear') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2dot4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('messages.totalRevenue') }}</div>
                <h3 class="fw-bold text-success mb-0">{{ $currencySymbol }}{{ number_format($total_revenue, 2) }}</h3>
                <small class="text-muted">{{ __('messages.collected') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('messages.totalInvoices') }}</div>
                <h3 class="fw-bold text-primary mb-0">{{ $invoices_stats->count }}</h3>
                <small class="text-muted">{{ __('messages.generated') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('messages.avgInvoice') }}</div>
                <h3 class="fw-bold text-info mb-0">{{ $currencySymbol }}{{ number_format($invoices_stats->avg_amount, 2) }}</h3>
                <small class="text-muted">{{ __('messages.perPatient') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('messages.paidRatio') }}</div>
                <h3 class="fw-bold {{ $paid_percentage >= 90 ? 'text-success' : 'text-warning' }} mb-0">
                    {{ number_format($paid_percentage, 1) }}%
                </h3>
                <small class="text-muted">{{ __('messages.collectedBilled') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('messages.pendingAmount') }}</div>
                <h3 class="fw-bold text-danger mb-0">{{ $currencySymbol }}{{ number_format($invoices_stats->pending_amount, 2) }}</h3>
                <small class="text-muted">{{ __('messages.unpaidBalances') }}</small>
            </div>
        </div>
    </div>
</div>

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
                            <span class="fw-bold">{{ $currencySymbol }}{{ number_format($amount, 2) }}</span>
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
                                <td class="text-end pe-4 fw-bold">{{ $currencySymbol }}{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">{{ __('messages.noTransactions') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($payments->hasPages())
                <div class="px-3 py-2">
                    {{ $payments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>
<script>
    function setDateRange(range) {
        const today = new Date();
        let from, to;
        const formatDate = d => d.toISOString().split('T')[0];
        
        to = formatDate(today);
        
        const start = new Date();
        switch(range) {
            case 'today':
                from = formatDate(today);
                break;
            case 'week':
                start.setDate(today.getDate() - today.getDay());
                from = formatDate(start);
                break;
            case 'month':
                from = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                break;
            case 'year':
                from = formatDate(new Date(today.getFullYear(), 0, 1));
                break;
        }
        
        document.querySelector('input[name="date_from"]').value = from;
        document.querySelector('input[name="date_to"]').value = to;
        document.querySelector('form').submit();
    }

    function getChartColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const s = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();
        const secondary = s('--secondary');
        const success = s('--success');
        const info = s('--info');
        const warning = s('--warning');
        const danger = s('--danger');
        return {
            grid: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
            tick: isDark ? s('--text-secondary') : '#555555',
            border: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
            tooltipBg: isDark ? s('--white') : '#ffffff',
            tooltipText: isDark ? s('--text-primary') : '#2c2c2c',
            lineColor: secondary || '#0f3d3e',
            fillGradient: isDark ? (secondary ? secondary + '20' : 'rgba(15,61,62,0.08)') : 'rgba(15,61,62,0.08)',
            doughnutColors: [
                secondary || '#0f3d3e',
                success || '#2e5d34',
                info || '#3d5a80',
                warning || '#bf8c30',
                danger || '#8b3a3a'
            ]
        };
    }

    const isRtl = document.documentElement.dir === 'rtl';
    const chartFont = isRtl ? 'Tajawal' : 'Plus Jakarta Sans';
    const cc = getChartColors();

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
