@extends('layouts.dashboard')
@section('title', __('messages.reports') . ' / ' . __('messages.outstandingInvoices'))
@section('page-title', __('messages.reports') . ' / ' . __('messages.outstandingInvoices'))
@section('content')
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mb-3">
    <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-1"></i> {{ __('messages.backToReports') }}
</a>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">{{ __('messages.outstandingInvoices') }}</h5>
        <div class="text-danger fw-bold">{{ __('messages.totalDue') }}: {{ $currencySymbol }}{{ number_format($total_outstanding, 2) }}</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>{{ __('messages.dueDateColumn') }}</th><th>{{ __('messages.invoiceColumn') }}</th><th>{{ __('messages.patientColumn') }}</th><th>{{ __('messages.totalColumn') }}</th><th>{{ __('messages.paidColumn') }}</th><th>{{ __('messages.balanceColumn') }}</th><th>{{ __('messages.actionColumn') }}</th></tr></thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td class="text-danger">{{ $inv->due_date->format('M d, Y') }}</td>
                        <td>{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->patient->name }}</td>
                        <td>{{ $currencySymbol }}{{ number_format($inv->total, 2) }}</td>
                        <td>{{ $currencySymbol }}{{ number_format($inv->amount_paid, 2) }}</td>
                        <td class="fw-bold text-danger">{{ $currencySymbol }}{{ number_format($inv->balance, 2) }}</td>
                        <td><a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">{{ __('messages.viewAction') }}</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">{{ __('messages.noTransactions') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
        <div class="px-3 py-2 border-top">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
