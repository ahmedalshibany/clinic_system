@extends('layouts.dashboard')
@section('title', 'Outstanding Invoices')
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between">
        <h5 class="mb-0">Outstanding Invoices</h5>
        <div class="text-danger fw-bold">Total Due: ${{ number_format($total_outstanding, 2) }}</div>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead><tr><th>Due Date</th><th>Invoice #</th><th>Patient</th><th>Total</th><th>Paid</th><th>Balance</th><th>Action</th></tr></thead>
            <tbody>
                @foreach($invoices as $inv)
                <tr>
                    <td class="text-danger">{{ $inv->due_date->format('M d, Y') }}</td>
                    <td>{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->patient->name }}</td>
                    <td>${{ number_format($inv->total, 2) }}</td>
                    <td>${{ number_format($inv->amount_paid, 2) }}</td>
                    <td class="fw-bold text-danger">${{ number_format($inv->balance, 2) }}</td>
                    <td><a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
