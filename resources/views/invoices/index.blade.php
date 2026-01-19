@extends('layouts.dashboard')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" data-i18n="billingAndInvoices">Billing & Invoices</h5>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> <span data-i18n="createInvoice">Create Invoice</span>
                </a>
            </div>
            <div class="card-body">
                {{-- Filters --}}
                <form action="{{ route('invoices.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search Invoice #" value="{{ request('search') }}" data-i18n-placeholder="searchInvoice">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="" data-i18n="allStatus">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }} data-i18n="draft">Draft</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }} data-i18n="sent">Sent</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }} data-i18n="paid">Paid</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }} data-i18n="partial">Partial</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }} data-i18n="overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}" data-i18n-placeholder="fromDate">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}" data-i18n-placeholder="toDate">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-secondary w-100" data-i18n="filter">Filter</button>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th data-i18n="invoiceNum">Invoice #</th>
                                <th data-i18n="patient">Patient</th>
                                <th data-i18n="date">Date</th>
                                <th data-i18n="total">Total</th>
                                <th data-i18n="paidAmount">Paid</th>
                                <th data-i18n="balance">Balance</th>
                                <th data-i18n="status">Status</th>
                                <th class="text-end" data-i18n="actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr>
                                <td class="fw-bold text-primary">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">{{ $invoice->invoice_number }}</a>
                                </td>
                                <td>
                                    {{ $invoice->patient->name }}
                                    <small class="d-block text-muted">{{ $invoice->patient->patient_code }}</small>
                                </td>
                                <td>
                                    {{ $invoice->created_at->format('M d, Y') }}
                                </td>
                                <td class="fw-bold">${{ number_format($invoice->total, 2) }}</td>
                                <td class="text-success">${{ number_format($invoice->amount_paid, 2) }}</td>
                                <td class="text-danger fw-bold">${{ number_format($invoice->balance, 2) }}</td>
                                <td>
                                    @switch($invoice->status)
                                        @case('paid') <span class="badge bg-success" data-i18n="paid">Paid</span> @break
                                        @case('partial') <span class="badge bg-warning text-dark" data-i18n="partial">Partial</span> @break
                                        @case('overdue') <span class="badge bg-danger" data-i18n="overdue">Overdue</span> @break
                                        @case('sent') <span class="badge bg-info text-dark" data-i18n="sent">Sent</span> @break
                                        @case('cancelled') <span class="badge bg-secondary" data-i18n="cancelled">Cancelled</span> @break
                                        @default <span class="badge bg-light text-dark border" data-i18n="draft">Draft</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
                                        @if($invoice->status == 'draft')
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        @endif
                                        <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Print"><i class="fas fa-print"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted" data-i18n="noInvoices">No invoices found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
