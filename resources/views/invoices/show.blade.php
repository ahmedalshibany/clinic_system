@extends('layouts.dashboard')

@section('title', __('messages.invoice_details') . ' - ' . $invoice->invoice_number)
@section('page-title', __('messages.invoice_details'))
@section('page-i18n', 'invoice_details')

@section('content')
<div>
    <a href="{{ smartBack('invoices.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-1"></i> {{ __('messages.backToInvoices') }}
    </a>
</div>
<div class="row">
    <div class="col-lg-9">
        <div class="card" style="box-shadow: var(--shadow-soft); border-radius: var(--radius, 10px);">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0 text-primary fw-bold">{{ $invoice->invoice_number }}</h5>
                    @if($invoice->status === 'paid')
                        <span class="badge" style="background-color: rgba(46, 93, 52, 0.1); color: var(--success, #2e5d34); border-radius: 6px;">{{ __('messages.paid') }}</span>
                    @else
                        <span class="badge" style="background-color: rgba(136, 136, 136, 0.1); color: #888; border-radius: 6px; border: 1px solid var(--border-hairline);">{{ __('messages.cancelled') }}</span>
                    @endif
                </div>
                <div>
                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i> {{ __('messages.print') }}</a>
                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-primary btn-sm ms-1"><i class="fas fa-file-pdf me-1"></i> {{ __('messages.download_pdf') }}</a>
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary btn-sm ms-1"><i class="fas fa-edit me-1"></i> {{ __('messages.edit') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="mb-3 text-muted text-uppercase small ls-1">{{ __('messages.bill_to') }}</h6>
                        <div>
                            <strong>{{ $invoice->patient->name }}</strong>
                        </div>
                        <div>{{ $invoice->patient->phone }}</div>
                        <div>{{ $invoice->patient->email }}</div>
                        <div class="mt-2 text-muted small">{{ __('messages.patient_id_colon') }} {{ $invoice->patient->patient_code }}</div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="mb-3 text-muted text-uppercase small ls-1">{{ __('messages.invoice_details') }}</h6>
                        <ul class="list-unstyled">
                            <li>{{ __('messages.date') }}: <strong>{{ $invoice->created_at->format('M d, Y') }}</strong></li>
                            <li>{{ __('messages.due_date') }}: <strong>{{ $invoice->due_date->format('M d, Y') }}</strong></li>
                            @if($invoice->appointment)
                            <li>{{ __('messages.appointment') }}: <strong>#{{ $invoice->appointment_id }}</strong></li>
                            @endif
                            <li class="mt-2">{{ __('messages.created_by') }}: {{ $invoice->creator->name }}</li>
                        </ul>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle" style="border-bottom: 1px solid var(--border-hairline);">
                        <thead style="background-color: var(--panel-bg, #fcfbfa); color: var(--secondary); font-weight: 600;">
                            <tr>
                                <th>{{ __('messages.description') }}</th>
                                <th class="text-center" width="10%">{{ __('messages.qty') }}</th>
                                <th class="text-end" width="15%">{{ __('messages.unit_price') }}</th>
                                <th class="text-end" width="15%">{{ __('messages.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>
                                    @if($item->service)
                                        <span class="badge" style="background-color: rgba(0,0,0,0.04); color: var(--text-secondary); border-radius: 4px;">{{ $item->service->code }}</span>
                                    @endif
                                    {{ $item->description }}
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ $currencySymbol }}{{ number_format($item->total, 2) }}</td>
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
                                <td>{{ __('messages.subtotal') }}:</td>
                                <td class="text-end fw-bold">{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                            <tr>
                                <td class="text-success">{{ __('messages.discount') }} ({{ number_format($invoice->discount_percent, 1) }}%):</td>
                                <td class="text-end text-success">-{{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->tax_amount > 0)
                            <tr>
                                <td>{{ __('messages.tax') }} ({{ number_format($invoice->tax_percent, 1) }}%):</td>
                                <td class="text-end">+{{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="border-top border-dark">
                                <td class="fs-5 fw-bold">{{ __('messages.total') }}:</td>
                                <td class="text-end fs-5 fw-bold">{{ $currencySymbol }}{{ number_format($invoice->total, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('messages.amount_paid') }}:</td>
                                <td class="text-end text-success fw-bold">{{ $currencySymbol }}{{ number_format($invoice->amount_paid, 2) }}</td>
                            </tr>
                            <tr class="">
                                <td class="fw-bold">{{ __('messages.balance_due') }}:</td>
                                <td class="text-end fw-bold text-danger">{{ $currencySymbol }}{{ number_format($invoice->balance, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar / Actions -->
    <div class="col-lg-3">
        @if($invoice->status == 'cancelled')
        <div class="card" style="box-shadow: var(--shadow-soft); border-radius: var(--radius, 10px);">
            <div class="card-body">
                <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-dollar-sign me-2"></i> {{ __('messages.record_payment') }}
                </button>
            </div>
        </div>
        @endif

        <!-- Payment History -->
        <div class="card" style="box-shadow: var(--shadow-soft); border-radius: var(--radius, 10px);">
            <div class="card-header   py-3">
                <h6 class="mb-0">{{ __('messages.payment_history') }}</h6>
            </div>
            <div class="card-body p-0">
                @if($invoice->payments->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($invoice->payments as $payment)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-success">{{ $currencySymbol }}{{ number_format($payment->amount, 2) }}</span>
                            <small class="text-muted">{{ $payment->payment_date->format('M d') }}</small>
                        </div>
                        <div class="small text-muted">
                            {{ __("messages.{$payment->payment_method}") }}
                            @if($payment->reference_number)
                             - {{ __('messages.ref_colon') }} {{ $payment->reference_number }}
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-3 text-center text-muted small">{{ __('messages.no_payments_yet') }}</div>
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
                <h5 class="modal-title">{{ __('messages.record_payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="amount" value="{{ $invoice->total }}">
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.payment_amount') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ $currencySymbol }}</span>
                        <input type="text" class="form-control" value="{{ number_format($invoice->total, 2) }}" readonly>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.date') }}</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.payment_method') }}</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash">{{ __('messages.cash') }}</option>
                        <option value="card">{{ __('messages.credit_card') }}</option>
                        <option value="bank_transfer">{{ __('messages.bank_transfer') }}</option>
                        <option value="insurance">{{ __('messages.insurance') }}</option>
                        <option value="other">{{ __('messages.other') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.reference_transaction_id') }}</label>
                    <input type="text" name="reference_number" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.notes') }}</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-success">{{ __('messages.save_payment') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
