<!DOCTYPE html>
@php $locale = app()->getLocale(); $isRtl = $locale === 'ar'; @endphp
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.invoice_details') }} - {{ $invoice->invoice_number }}</title>
    <style>
        :root {
            --font-family: {{ $isRtl ? "'Noto Sans Arabic', 'Tajawal', sans-serif" : "'Inter', 'Helvetica Neue', Arial, sans-serif" }};
            --border-hairline: 1px solid #d0d0d0;
            --text-primary: #1a1a1a;
            --text-muted: #555;
            --text-success: #0d7c3f;
            --text-danger: #c00;
            --bg-stripe: #f7f8fa;
            --page-padding: 2.4rem;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-family);
            color: var(--text-primary);
            font-size: 13px;
            line-height: 1.6;
            padding: 0;
            background: #f2f3f5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .invoice-wrapper {
            max-width: 850px;
            margin: 2rem auto;
            background: #fff;
            padding: var(--page-padding);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-radius: 6px;
        }
        .no-print { text-align: center; margin-bottom: 1.2rem; }
        .btn-print {
            background: #1a2a3a;
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 6px;
            font-size: 14px;
            font-family: var(--font-family);
            cursor: pointer;
        }
        .btn-print:hover { background: #2a3a4a; }

        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 1.6rem;
            border-bottom: 2px solid #1a2a3a;
            margin-bottom: 1.6rem;
        }
        .clinic-block h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1a2a3a;
            margin-bottom: 4px;
        }
        .clinic-block .clinic-detail {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }
        .invoice-meta {
            text-align: {{ $isRtl ? 'start' : 'end' }};
        }
        .invoice-meta h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1a2a3a;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .invoice-meta .meta-row {
            font-size: 12px;
            color: var(--text-primary);
            line-height: 1.7;
        }
        .invoice-meta .meta-row strong {
            font-weight: 600;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-top: 4px;
        }
        .status-paid { background: #e3f5eb; color: #0d7c3f; border: 1px solid #b2dfc9; }
        .status-cancelled { background: #f5f5f5; color: #888; border: 1px solid #ddd; }

        /* Bill To */
        .bill-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.6rem;
            flex-wrap: wrap;
            gap: 1.2rem;
        }
        .bill-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .bill-block .value {
            font-weight: 600;
            font-size: 14px;
        }
        .bill-block .sub-value {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Table */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.6rem;
        }
        table.items thead th {
            border-bottom: var(--border-hairline);
            border-top: var(--border-hairline);
            padding: 10px 8px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--text-muted);
            font-weight: 600;
            text-align: {{ $isRtl ? 'right' : 'left' }};
            background: var(--bg-stripe);
        }
        table.items thead th.numeric {
            text-align: {{ $isRtl ? 'left' : 'right' }};
        }
        table.items tbody td {
            padding: 9px 8px;
            border-bottom: var(--border-hairline);
            font-size: 13px;
        }
        table.items tbody td.numeric {
            text-align: {{ $isRtl ? 'left' : 'right' }};
            font-variant-numeric: tabular-nums;
        }
        table.items tbody tr:last-child td {
            border-bottom: var(--border-hairline);
        }

        /* Totals */
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.6rem;
        }
        table.totals {
            width: 280px;
            border-collapse: collapse;
        }
        table.totals td {
            padding: 5px 0;
            font-size: 13px;
            text-align: {{ $isRtl ? 'left' : 'right' }};
        }
        table.totals td.label {
            color: var(--text-muted);
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }
        table.totals td.amount {
            font-variant-numeric: tabular-nums;
        }
        table.totals .divider td {
            border-top: 2px solid #1a2a3a;
            padding-top: 6px;
        }
        table.totals .grand-total td {
            font-size: 16px;
            font-weight: 700;
            padding-top: 4px;
        }
        table.totals .balance-due td {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-danger);
            padding-top: 4px;
        }
        table.totals .amount-paid td {
            color: var(--text-success);
        }

        /* Notes */
        .notes-block {
            border-top: var(--border-hairline);
            padding-top: 1rem;
            margin-bottom: 1rem;
        }
        .notes-block h4 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .notes-block p {
            font-size: 12px;
            color: var(--text-primary);
        }

        /* Footer */
        .invoice-footer {
            border-top: var(--border-hairline);
            padding-top: 1rem;
            text-align: center;
            font-size: 11px;
            color: var(--text-muted);
        }

        @media print {
            body { background: #fff; }
            .invoice-wrapper {
                box-shadow: none;
                border-radius: 0;
                margin: 0 auto;
                padding: var(--page-padding);
            }
            .no-print { display: none; }
            .invoice-header { border-bottom-color: #000; }
            table.totals .divider td { border-top-color: #000; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">{{ __('messages.print') }}</button>
    </div>

    <div class="invoice-wrapper">

        {{-- Header --}}
        <div class="invoice-header">
            <div class="clinic-block">
                @php
                    $clinicName = $isRtl
                        ? (\App\Models\Setting::get('clinic_name_ar', config('app.name', 'العيادة')) ?: config('app.name', 'العيادة'))
                        : (\App\Models\Setting::get('clinic_name', config('app.name', 'Clinic')) ?: config('app.name', 'Clinic'));
                    $clinicAddress = \App\Models\Setting::get('clinic_address', '');
                    $clinicPhone = \App\Models\Setting::get('clinic_phone', '');
                    $clinicEmail = \App\Models\Setting::get('clinic_email', '');
                @endphp
                <h1>{{ $clinicName }}</h1>
                @if($clinicAddress)
                    <div class="clinic-detail">{{ $clinicAddress }}</div>
                @endif
                @if($clinicPhone)
                    <div class="clinic-detail">{{ $clinicPhone }}</div>
                @endif
                @if($clinicEmail)
                    <div class="clinic-detail">{{ $clinicEmail }}</div>
                @endif
            </div>
            <div class="invoice-meta">
                <h2>{{ __('messages.invoice_details') }}</h2>
                <div class="meta-row">{{ __('messages.invoice_number') }}: <strong>{{ $invoice->invoice_number }}</strong></div>
                <div class="meta-row">{{ __('messages.date') }}: <strong>{{ $invoice->created_at->format('M d, Y') }}</strong></div>
                <div class="meta-row">{{ __('messages.due_date') }}: <strong>{{ $invoice->due_date->format('M d, Y') }}</strong></div>
                @if($invoice->appointment)
                    <div class="meta-row">{{ __('messages.appointment') }}: <strong>#{{ $invoice->appointment_id }}</strong></div>
                @endif
                <div class="meta-row">{{ __('messages.status') }}:
                    @if($invoice->status === 'paid')
                        <span class="status-badge status-paid">{{ __('messages.invoice_paid') }}</span>
                    @else
                        <span class="status-badge status-cancelled">{{ __('messages.invoice_cancelled') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bill To & Meta --}}
        <div class="bill-section">
            <div class="bill-block">
                <h3>{{ __('messages.bill_to') }}</h3>
                <div class="value">{{ $invoice->patient->name }}</div>
                @if($invoice->patient->phone)
                    <div class="sub-value">{{ $invoice->patient->phone }}</div>
                @endif
                @if($invoice->patient->email)
                    <div class="sub-value">{{ $invoice->patient->email }}</div>
                @endif
                <div class="sub-value" style="margin-top:4px;">{{ __('messages.patient_id_colon') }} {{ $invoice->patient->patient_code }}</div>
            </div>
            @if($invoice->appointment)
            <div class="bill-block">
                <h3>{{ __('messages.appointment') }}</h3>
                <div class="sub-value">{{ __('messages.date') }}: {{ $invoice->appointment->date->format('M d, Y') }}</div>
                <div class="sub-value">{{ __('messages.type') }}: {{ $invoice->appointment->type }}</div>
            </div>
            @endif
            <div class="bill-block">
                <h3>{{ __('messages.created_by') }}</h3>
                <div class="sub-value">{{ $invoice->creator->name }}</div>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="items">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th>{{ __('messages.description') }}</th>
                    <th class="numeric" style="width:10%;">{{ __('messages.qty') }}</th>
                    <th class="numeric" style="width:16%;">{{ __('messages.unit_price') }}</th>
                    <th class="numeric" style="width:16%;">{{ __('messages.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        @if($item->service)
                            <span style="font-weight:600;">{{ $item->service->name }}</span><br>
                        @endif
                        {{ $item->description }}
                    </td>
                    <td class="numeric">{{ $item->quantity }}</td>
                    <td class="numeric">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="numeric">{{ $currencySymbol }}{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals-wrap">
            <table class="totals">
                <tr>
                    <td class="label">{{ __('messages.subtotal') }}:</td>
                    <td class="amount">{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td class="label">{{ __('messages.discount') }} ({{ number_format($invoice->discount_percent, 1) }}%):</td>
                    <td class="amount" style="color:var(--text-success);">-{{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->tax_amount > 0)
                <tr>
                    <td class="label">{{ __('messages.tax') }} ({{ number_format($invoice->tax_percent, 1) }}%):</td>
                    <td class="amount">+{{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="divider">
                    <td class="label" style="font-weight:700;">{{ __('messages.total') }}:</td>
                    <td class="amount" style="font-weight:700;">{{ $currencySymbol }}{{ number_format($invoice->total, 2) }}</td>
                </tr>
                <tr class="amount-paid">
                    <td class="label">{{ __('messages.amount_paid') }}:</td>
                    <td class="amount">{{ $currencySymbol }}{{ number_format($invoice->amount_paid, 2) }}</td>
                </tr>
                <tr class="balance-due">
                    <td class="label">{{ __('messages.balance_due') }}:</td>
                    <td class="amount">{{ $currencySymbol }}{{ number_format($invoice->balance, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- Notes --}}
        @if($invoice->notes)
        <div class="notes-block">
            <h4>{{ __('messages.notes') }}</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="invoice-footer">
            <p>{{ __('messages.thank_you_business', ['clinic' => $clinicName]) }}</p>
        </div>

    </div>

</body>
</html>