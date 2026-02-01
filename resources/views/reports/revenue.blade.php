@extends('layouts.dashboard')

@section('title', 'Detailed Revenue Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Revenue Report</h4>
    <div>
        <button onclick="window.print()" class="btn btn-outline-secondary me-2">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <a href="{{ route('reports.export.excel', ['report' => 'revenue'] + request()->all()) }}" class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Export Excel
        </a>
    </div>
</div>

<!-- Date Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small d-block">Quick Filters</label>
                <div class="btn-group w-100">
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('today')">Today</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('week')">This Week</button>
                    <button type="button" class="btn btn-outline-primary active" onclick="setDateRange('month')">This Month</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('year')">This Year</button>
                </div>
                <button class="btn btn-primary ms-2 d-none" id="applyBtn">Apply</button> <!-- Hidden, triggered by JS -->
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2dot4"> <!-- Custom 5 col layout class or just col-md-3 -->
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">Total Revenue</div>
                <h3 class="fw-bold text-success mb-0">${{ number_format($total_revenue, 2) }}</h3>
                <small class="text-muted">Collected</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">Total Invoices</div>
                <h3 class="fw-bold text-primary mb-0">{{ $invoices_stats->count }}</h3>
                <small class="text-muted">Generated</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">Avg Invoice</div>
                <h3 class="fw-bold text-info mb-0">${{ number_format($invoices_stats->avg_amount, 2) }}</h3>
                <small class="text-muted">Per Patient</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">Paid Ratio</div>
                <h3 class="fw-bold {{ $paid_percentage >= 90 ? 'text-success' : 'text-warning' }} mb-0">
                    {{ number_format($paid_percentage, 1) }}%
                </h3>
                <small class="text-muted">Collected/Billed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">Pending Amount</div>
                <h3 class="fw-bold text-danger mb-0">${{ number_format($invoices_stats->pending_amount, 2) }}</h3>
                <small class="text-muted">Unpaid Balances</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart Section -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Revenue Trend</h6>
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
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Payment Methods</h6>
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
                            <span class="fw-bold">${{ number_format($amount, 2) }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @else
                <div class="text-center text-muted py-5">No payment data available</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Detailed Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Transaction Details</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Invoice #</th>
                                <th>Patient</th>
                                <th>Method</th>
                                <th>Received By</th>
                                <th class="text-end pe-4">Amount</th>
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
                                        <div class="avatar-sm rounded-circle bg-light text-primary d-flex align-items-center justify-content-center me-2">
                                            {{ substr($payment->invoice->patient->name, 0, 1) }}
                                        </div>
                                        {{ $payment->invoice->patient->name }}
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($payment->payment_method) }}</span></td>
                                <td>{{ $payment->receiver->name ?? 'System' }}</td>
                                <td class="text-end pe-4 fw-bold">${{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No transactions found for this period.</td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Set Date Range Helper
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

    // Revenue Chart
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chart_labels),
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: @json($chart_data),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2] } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // methodChart Chart
    const ctx2 = document.getElementById('methodChart');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: @json($revenue_by_method->keys()),
                datasets: [{
                    data: @json($revenue_by_method->values()),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }
</script>
@endsection
