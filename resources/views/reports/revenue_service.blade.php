@extends('layouts.dashboard')
@section('title', 'Revenue by Service')
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Sales by Service</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead><tr><th>Service</th><th>Qty Sold</th><th class="text-end">Total Sales</th></tr></thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td>{{ $row->service_name }}</td>
                    <td>{{ $row->total_qty }}</td>
                    <td class="text-end fw-bold text-primary">${{ number_format($row->total_sales, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
