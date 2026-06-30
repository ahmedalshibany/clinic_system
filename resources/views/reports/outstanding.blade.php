@extends('layouts.dashboard')

@section('page-title', __('messages.outstanding_report'))
@section('page-i18n', 'outstandingInvoices')

@section('content')
<style>
    /* 1. Flush Baseline Action Elements Wrapper */
    .outstanding-actions-row {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        margin-bottom: 1.5rem !important;
        min-height: 46px !important;
    }
    .outstanding-actions-row .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 42px !important;
        border-radius: 10px !important;
        font-weight: 500 !important;
    }

    /* 2. Symmetrical Filter Wrapper */
    .outstanding-filter-wrapper {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 1rem !important;
        width: 100% !important;
    }

    /* 3. HARD GRID LOCKDOWN: Exactly 4 columns across desktop viewports */
    .outstanding-metrics-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 16px !important;
        width: 100% !important;
        margin-bottom: 2rem !important;
    }
    .outstanding-premium-card {
        background: var(--white) !important;
        border-radius: 16px !important;
        padding: 1.5rem !important;
        height: 100% !important;
        box-shadow: var(--shadow-soft, 0 4px 20px rgba(0, 0, 0, 0.02)) !important;
        border: none !important;
        text-align: center !important;
    }

    /* Absolute Transition Cover to shield layout from dynamic JS direction flips */
    .outstanding-metrics-grid,
    .outstanding-metrics-grid *,
    .outstanding-actions-row,
    .outstanding-filter-wrapper {
        transition: none !important;
        animation: none !important;
    }

    /* Luxury Financial Color Hierarchy for Debts & Outstanding Dues */
    .outstanding-token-total { color: #a94442 !important; font-weight: 700; } /* Premium Soft Terracotta Brick Wine for Total Debt */
    .outstanding-token-overdue { color: #cc0000 !important; font-weight: 700; } /* Alert Crimson Red strictly scoped to critical overdue counts */
    .outstanding-token-debtor { color: #b07d2a !important; font-weight: 700; } /* Warm Local Muted Ochre Gold for top debtor info */
    .outstanding-token-bills { color: #2e5b5c !important; font-weight: 700; } /* Muted Slate Teal for active tracked documents */

    input[type="date"].form-control-luxury {
        padding: 0.6rem 1rem !important;
        border-radius: 10px !important;
        background-color: rgba(15, 61, 62, 0.02) !important;
        border: 1px solid rgba(15, 61, 62, 0.12) !important;
        color: var(--secondary) !important;
        font-weight: 500;
    }
</style>

<div class="outstanding-actions-row">
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary gap-2">
        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs"></i>
        <span class="text-sm fw-medium">{{ __('messages.backToReports') }}</span>
    </a>

    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-light border" style="color: var(--secondary); background: var(--white);"><i class="fas fa-print ml-2"></i>{{ __('messages.print') }}</button>
        <button class="btn btn-light border" style="color: var(--secondary); background: var(--white);"><i class="fas fa-file-excel ml-2"></i>{{ __('messages.exportExcel') }}</button>
    </div>
</div>

<form action="{{ route('reports.outstanding') }}" method="GET" id="outstandingFilterForm" class="w-100">
    <input type="hidden" name="quick_filter" id="quickFilterInput" value="{{ request('quick_filter') }}">

    <div class="card border-0 p-4 mb-4 shadow-sm" style="background: var(--white); border-radius: 16px;">
        <div class="outstanding-filter-wrapper">
            <div class="d-flex flex-wrap gap-2">
                <button type="button" onclick="applyQuickFilter('today')" class="btn btn-sm {{ request('quick_filter') == 'today' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'today' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.today') }}</button>
                <button type="button" onclick="applyQuickFilter('this_week')" class="btn btn-sm {{ request('quick_filter') == 'this_week' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'this_week' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisWeek') }}</button>
                <button type="button" onclick="applyQuickFilter('this_month')" class="btn btn-sm {{ (request('quick_filter') == 'this_month' || !request('quick_filter')) ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ (request('quick_filter') == 'this_month' || !request('quick_filter')) ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisMonth') }}</button>
                <button type="button" onclick="applyQuickFilter('this_year')" class="btn btn-sm {{ request('quick_filter') == 'this_year' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'this_year' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisYear') }}</button>
            </div>
            <div class="d-flex gap-3 justify-content-md-end mb-3 mb-md-0">
                <div style="width: 160px;">
                    <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.fromDate') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="document.getElementById('quickFilterInput').value=''; document.getElementById('outstandingFilterForm').submit();" class="form-control form-control-luxury">
                </div>
                <div style="width: 160px;">
                    <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.toDate') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" onchange="document.getElementById('quickFilterInput').value=''; document.getElementById('outstandingFilterForm').submit();" class="form-control form-control-luxury">
                </div>
            </div>
        </div>
    </div>
</form>

<div class="outstanding-metrics-grid">
    <div class="outstanding-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.total_outstanding') }}</span>
        <h3 class="outstanding-token-total mb-1"><span class="text-nowrap">{{ $currencySymbol }}{{ number_format($total_outstanding, 2) }}</span></h3>
        <small class="text-muted text-xs d-block">{{ __('messages.uncollected_funds') }}</small>
    </div>

    <div class="outstanding-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.overdue_invoices') }}</span>
        <h3 class="outstanding-token-overdue mb-1" style="font-size: 1.6rem;">{{ $overdue_invoices_count }}</h3>
        <small class="text-muted text-xs d-block">{{ __('messages.past_due_date') }}</small>
    </div>

    <div class="outstanding-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.top_debtor') }}</span>
        <h3 class="outstanding-token-debtor mb-1 text-truncate" style="font-size: 1.3rem; max-width: 100%;" title="{{ $top_debtor_patient }}">{{ $top_debtor_patient }}</h3>
        <small class="text-muted text-xs d-block">{{ __('messages.highest_balance') }}</small>
    </div>

    <div class="outstanding-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.pending_bills') }}</span>
        <h3 class="outstanding-token-bills mb-1" style="font-size: 1.6rem;">{{ $total_pending_bills }}</h3>
        <small class="text-muted text-xs d-block">{{ __('messages.active_receivables') }}</small>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-secondary text-xs fw-bold border-0">
                        <th class="py-3">{{ __('messages.dueDateColumn') }}</th>
                        <th class="py-3">{{ __('messages.invoiceNumColumn') }}</th>
                        <th class="py-3">{{ __('messages.patientColumn') }}</th>
                        <th class="py-3 text-end">{{ __('messages.totalColumn') }}</th>
                        <th class="py-3 text-end">{{ __('messages.paidColumn') }}</th>
                        <th class="py-3 text-end">{{ __('messages.balanceColumn') }}</th>
                        <th class="py-3 text-center">{{ __('messages.statusColumn') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr class="border-bottom">
                            <td class="py-3 text-secondary text-sm">
                                {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}
                            </td>
                            <td class="py-3 fw-semibold text-dark text-sm">
                                #{{ $invoice->invoice_number }}
                            </td>
                            <td class="py-3 fw-medium text-dark text-sm">
                                {{ $invoice->patient->name ?? __('messages.unassigned') }}
                            </td>
                            <td class="py-3 text-end text-sm text-secondary">
                                {{ $currencySymbol }}{{ number_format($invoice->total, 2) }}
                            </td>
                            <td class="py-3 text-end text-sm text-success">
                                {{ $currencySymbol }}{{ number_format($invoice->amount_paid, 2) }}
                            </td>
                            <td class="py-3 text-end text-sm fw-bold" style="color: #a94442;">
                                {{ $currencySymbol }}{{ number_format($invoice->total - $invoice->amount_paid, 2) }}
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 py-1 text-xs">{{ __('messages.status_cancelled') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted text-sm fw-medium">
                                {{ __('messages.no_data_available') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {!! $invoices->links() !!}
            </div>
        @endif
    </div>
</div>

<script>
function applyQuickFilter(filterValue) {
    document.getElementById('quickFilterInput').value = filterValue;
    document.getElementsByName('date_from')[0].value = '';
    document.getElementsByName('date_to')[0].value = '';
    document.getElementById('outstandingFilterForm').submit();
}
</script>
@endsection