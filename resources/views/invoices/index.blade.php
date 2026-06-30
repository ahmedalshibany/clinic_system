@extends('layouts.dashboard')

@section('title', __('messages.invoices'))
@section('page-title', __('messages.invoices'))
@section('page-i18n', 'invoices')

@section('content')

<!-- Action Toolbar -->
<div class="action-toolbar d-flex gap-3 flex-wrap align-items-center justify-content-between mb-4 fade-in">
    <form action="{{ route('invoices.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search') }}..." value="{{ request('search') }}">
        </div>

        <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
            <option value="" {{ request('status') == '' ? 'selected' : '' }} data-i18n="allStatus">{{ __('messages.allStatus') }}</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }} data-i18n="paid">Paid</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }} data-i18n="cancelled">Cancelled</option>
        </select>

        <input type="date" name="date_from" class="form-control" style="width: auto;" value="{{ request('date_from') }}" data-i18n-placeholder="fromDate">
        <input type="date" name="date_to" class="form-control" style="width: auto;" value="{{ request('date_to') }}" data-i18n-placeholder="toDate">
    </form>

    <a href="{{ route('invoices.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <i class="fas fa-plus"></i>
        <span data-i18n="createInvoice">{{ __('messages.createInvoice') }}</span>
    </a>
</div>

<!-- Invoices Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="">
                    <tr>
                        <th class="ps-4 py-3" data-i18n="invoiceNum">Invoice #</th>
                        <th class="py-3" data-i18n="patient">Patient</th>
                        <th class="py-3" data-i18n="date">Date</th>
                        <th class="py-3" data-i18n="total">Total</th>
                        <th class="py-3" data-i18n="paid">Paid</th>
                        <th class="py-3" data-i18n="balance">Balance</th>
                        <th class="py-3" data-i18n="status">Status</th>
                        <th class="pe-4 py-3 text-center" data-i18n="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td class="ps-4 fw-bold">
                            <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">{{ $invoice->invoice_number }}</a>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $invoice->patient->name }}</span>
                            <small class="d-block text-muted">{{ $invoice->patient->patient_code }}</small>
                        </td>
                        <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                        <td class="fw-bold">{{ $currencySymbol }} {{ number_format($invoice->total, 2) }}</td>
                        <td class="text-success">{{ $currencySymbol }} {{ number_format($invoice->amount_paid, 2) }}</td>
                        <td class="text-danger fw-bold">{{ $currencySymbol }} {{ number_format($invoice->balance, 2) }}</td>
                        <td>
                            @if($invoice->status === 'paid')
                                <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 text-xs fw-semibold">{{ __('messages.invoice_paid') }}</span>
                            @else
                                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 py-1 text-xs fw-semibold">{{ __('messages.invoice_cancelled') }}</span>
                            @endif
                        </td>
                        <td class="pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-soft-info btn-sm" title="View" data-i18n-title="view">
                                    <i class="fas fa-eye"></i>
                                </a>
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-soft-primary btn-sm" title="Edit" data-i18n-title="edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-soft-secondary btn-sm" title="Print" data-i18n-title="print">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-file-invoice-dollar text-muted mb-3" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0" data-i18n="noInvoices">{{ __('messages.noInvoicesFound') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="pagination-controls">
            <div class="pagination-info">
                <span data-i18n="showing">{{ __('messages.showing') }}</span> <strong>{{ $invoices->firstItem() }}-{{ $invoices->lastItem() }}</strong> <span data-i18n="of">{{ __('messages.of') }}</span> <strong>{{ $invoices->total() }}</strong> <span data-i18n="invoices">{{ __('messages.invoices') }}</span>
            </div>
            <div class="d-flex gap-2">
                @if(!$invoices->onFirstPage())
                    <a href="{{ $invoices->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> <span data-i18n="previous">{{ __('messages.previous') }}</span></a>
                @endif
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="btn btn-light btn-sm"><span data-i18n="next">{{ __('messages.next') }}</span> <i class="fas fa-chevron-right"></i></a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
