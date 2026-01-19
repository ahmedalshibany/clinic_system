<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #eee;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .clinic-info h1 {
            color: #2F4156;
            margin: 0 0 5px;
            font-size: 24px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0 0 10px;
            color: #2F4156;
        }
        .bill-to {
            margin-bottom: 30px;
        }
        .bill-to h3 {
            margin: 0 0 5px;
            font-size: 16px;
            text-transform: uppercase;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #f8f9fa;
            border-bottom: 2px solid #2F4156;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .total-section {
            display: flex;
            justify-content: flex-end;
        }
        .total-table {
            width: 300px;
        }
        .total-table td {
            text-align: right;
            border: none;
            padding: 5px;
        }
        .total-table .final {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 16px;
            color: #000;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        @media print {
            .invoice-box {
                box-shadow: none;
                border: none;
            }
            .no-print {
                display: none;
            }
        }
        .btn {
            background: #2F4156;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div style="text-align: center;" class="no-print">
        <button onclick="window.print()" class="btn">Print Invoice</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="clinic-info">
                <h1>{{ config('app.name', 'Clinic Name') }}</h1>
                <div>123 Medical Center Drive</div>
                <div>Health City, HC 12345</div>
                <div>Phone: (555) 123-4567</div>
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <div>Invoice #: <strong>{{ $invoice->invoice_number }}</strong></div>
                <div>Date: {{ $invoice->created_at->format('M d, Y') }}</div>
                <div>Due Date: {{ $invoice->due_date->format('M d, Y') }}</div>
                <div>Status: {{ ucfirst($invoice->status) }}</div>
            </div>
        </div>

        <div class="bill-to">
            <h3>Bill To:</h3>
            <strong>{{ $invoice->patient->name }}</strong><br>
            {{ $invoice->patient->address ?? '' }}<br>
            {{ $invoice->patient->city ?? '' }}<br>
            {{ $invoice->patient->phone }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: center; width: 60px;">Qty</th>
                    <th style="text-align: right; width: 100px;">Price</th>
                    <th style="text-align: right; width: 100px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td>Subtotal:</td>
                    <td>${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td>Discount ({{ number_format($invoice->discount_percent, 1) }}%):</td>
                    <td>-${{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->tax_amount > 0)
                <tr>
                    <td>Tax ({{ number_format($invoice->tax_percent, 1) }}%):</td>
                    <td>+${{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="final">
                    <td>Total:</td>
                    <td>${{ number_format($invoice->total, 2) }}</td>
                </tr>
                <tr>
                    <td>Amount Paid:</td>
                    <td>${{ number_format($invoice->amount_paid, 2) }}</td>
                </tr>
                <tr style="color: #c00; font-weight: bold;">
                    <td>Balance Due:</td>
                    <td>${{ number_format($invoice->balance, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for choosing our clinic. Payment is due within 7 days.</p>
        </div>
    </div>
</body>
</html>
