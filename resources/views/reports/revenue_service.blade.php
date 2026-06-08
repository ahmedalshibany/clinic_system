@extends('layouts.dashboard')
@section('title', __('messages.reports') . ' / ' . __('messages.salesByServiceTitle'))
@section('page-title', __('messages.reports') . ' / ' . __('messages.salesByServiceTitle'))
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h5 class="mb-0">{{ __('messages.salesByServiceTitle') }}</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>{{ __('messages.serviceName') }}</th><th>{{ __('messages.qtySold') }}</th><th class="text-end">{{ __('messages.totalSales') }}</th></tr></thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td>{{ $row->service_name }}</td>
                    <td>{{ $row->total_qty }}</td>
                    <td class="text-end fw-bold text-primary">{{ __('messages.currencySymbol') }}{{ number_format($row->total_sales, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">{{ __('messages.noTransactions') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
