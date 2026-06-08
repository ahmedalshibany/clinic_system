@extends('layouts.dashboard')
@section('title', __('messages.reports') . ' / ' . __('messages.appointmentsReport'))
@section('page-title', __('messages.reports') . ' / ' . __('messages.appointmentsReport'))
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ __('messages.appointmentsReport') }}</h5>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            @forelse($status_stats as $status => $count)
            <div class="col-md-2 text-center">
                <h3 class="mb-0">{{ $count }}</h3>
                <small class="text-uppercase text-muted">{{ $status }}</small>
            </div>
            @empty
            <div class="col-12 text-center text-muted py-4">{{ __('messages.noTransactions') }}</div>
            @endforelse
        </div>
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>{{ __('messages.dateColumn') }}</th><th>{{ __('messages.patientColumn') }}</th><th>{{ __('messages.doctorColumn') }}</th><th>{{ __('messages.statusColumn') }}</th></tr></thead>
            <tbody>
                @forelse($appointments as $appt)
                <tr>
                    <td>{{ $appt->date->format('M d, Y') }} {{ $appt->time->format('H:i') }}</td>
                    <td>{{ $appt->patient->name }}</td>
                    <td>{{ $appt->doctor->user->name }}</td>
                    <td><span class="badge bg-secondary">{{ $appt->status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">{{ __('messages.noTransactions') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
