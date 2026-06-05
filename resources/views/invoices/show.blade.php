@extends('layouts.dashboard')

@section('title', __('Invoices') . ' / ' . $invoice->invoice_number)
@section('page-title', __('Invoices') . ' / ' . __('Details'))

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header   py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0 text-primary fw-bold">{{ $invoice->invoice_number }}</h5>
                    @switch($invoice->status)
                        @case('paid') <span class="badge bg-success">{{ __('Paid') }}</span> @break
                        @case('partial') <span class="badge bg-warning text-dark">{{ __('Partial') }}</span> @break
                        @case('overdue') <span class="badge bg-danger">{{ __('Overdue') }}</span> @break
                        @case('sent') <span class="badge bg-info text-dark">{{ __('Sent') }}</span> @break
                        @case('cancelled') <span class="badge bg-secondary">{{ __('Cancelled') }}</span> @break
                        @default <span class="badge   text-dark border">{{ __('Draft') }}</span>
                    @endswitch
                </div>
                <div>
                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i> {{ __('Print') }}</a>
                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-primary btn-sm ms-1"><i class="fas fa-file-pdf me-1"></i> {{ __('Download PDF') }}</a>
                    @if($invoice->status == 'draft')
                    <form action="{{ route('invoices.send', $invoice) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-paper-plane me-1"></i> {{ __('Mark Sent') }}</button>
                    </form>
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary btn-sm ms-1"><i class="fas fa-edit me-1"></i> {{ __('messages.edit') }}</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="mb-3 text-muted text-uppercase small ls-1">{{ __('Bill To') }}</h6>
                        <div>
                            <strong>{{ $invoice->patient->name }}</strong>
                        </div>
                        <div>{{ $invoice->patient->phone }}</div>
                        <div>{{ $invoice->patient->email }}</div>
                        <div class="mt-2 text-muted small">{{ __('Patient ID:') }} {{ $invoice->patient->patient_code }}</div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="mb-3 text-muted text-uppercase small ls-1">{{ __('Invoices') }} / {{ __('Details') }}</h6>
                        <ul class="list-unstyled">
                            <li>{{ __('Date:') }} <strong>{{ $invoice->created_at->format('M d, Y') }}</strong></li>
                            <li>{{ __('Due Date:') }} <strong>{{ $invoice->due_date->format('M d, Y') }}</strong></li>
                            @if($invoice->appointment)
                            <li>{{ __('Appointment:') }} <strong>#{{ $invoice->appointment_id }}</strong></li>
                            @endif
                            <li class="mt-2">{{ __('Created By:') }} {{ $invoice->creator->name }}</li>
                        </ul>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="">
                            <tr>
                                <th>{{ __('Description') }}</th>
                                <th class="text-center" width="10%">{{ __('Qty') }}</th>
                                <th class="text-end" width="15%">{{ __('Unit Price') }}</th>
                                <th class="text-end" width="15%">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>
                                    @if($item->service)
                                        <span class="badge bg-secondary-subtle text-dark me-1">{{ $item->service->code }}</span>
                                    @endif
                                    {{ $item->description }}
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        @if($invoice->notes)
                        <div class="alert alert-light border">
                            <h6 class="alert-heading text-muted small uppercase">{{ __('messages.notes') }}</h6>
                            <p class="mb-0 small">{{ $invoice->notes }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-5 offset-md-1">
                        <table class="table table-sm">
                            <tr>
                                <td>{{ __('Subtotal:') }}</td>
                                <td class="text-end fw-bold">${{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                            <tr>
                                <td class="text-success">{{ __('Discount') }} ({{ number_format($invoice->discount_percent, 1) }}%):</td>
                                <td class="text-end text-success">-${{ number_format($invoice->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->tax_amount > 0)
                            <tr>
                                <td>{{ __('Tax') }} ({{ number_format($invoice->tax_percent, 1) }}%):</td>
                                <td class="text-end">+${{ number_format($invoice->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="border-top border-dark">
                                <td class="fs-5 fw-bold">{{ __('Total:') }}</td>
                                <td class="text-end fs-5 fw-bold">${{ number_format($invoice->total, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Amount Paid:') }}</td>
                                <td class="text-end text-success fw-bold">${{ number_format($invoice->amount_paid, 2) }}</td>
                            </tr>
                            <tr class="">
                                <td class="fw-bold">{{ __('Balance Due:') }}</td>
                                <td class="text-end fw-bold text-danger">${{ number_format($invoice->balance, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar / Actions -->
    <div class="col-lg-3">
        @if($invoice->balance > 0 && $invoice->status != 'cancelled')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-dollar-sign me-2"></i> {{ __('Record Payment') }}
                </button>
            </div>
        </div>
        @endif

        <!-- Payment History -->
        <div class="card border-0 shadow-sm">
            <div class="card-header   py-3">
                <h6 class="mb-0">{{ __('Payment History') }}</h6>
            </div>
            <div class="card-body p-0">
                @if($invoice->payments->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($invoice->payments as $payment)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-success">${{ number_format($payment->amount, 2) }}</span>
                            <small class="text-muted">{{ $payment->payment_date->format('M d') }}</small>
                        </div>
                        <div class="small text-muted">
                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            @if($payment->reference_number)
                             - {{ __('Ref:') }} {{ $payment->reference_number }}
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-3 text-center text-muted small">{{ __('No payments recorded yet.') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('invoices.payment', $invoice) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Record Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Payment Amount') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="amount" class="form-control" step="0.01" max="{{ $invoice->balance }}" value="{{ $invoice->balance }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Date') }}</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Payment Method') }}</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="card">{{ __('Credit/Debit Card') }}</option>
                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                        <option value="insurance">{{ __('Insurance') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Reference / Transaction ID') }}</label>
                    <input type="text" name="reference_number" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.notes') }}</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-success">{{ __('Save Payment') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
