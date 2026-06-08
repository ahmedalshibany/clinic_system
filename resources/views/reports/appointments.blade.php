@extends('layouts.dashboard')
@section('title', __('messages.reports') . ' / ' . __('messages.appointmentsReport'))
@section('page-title', __('messages.reports') . ' / ' . __('messages.appointmentsReport'))
@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('messages.appointmentsReport') }}</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label small">{{ __('messages.dateFrom') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('messages.dateTo') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('messages.doctorColumn') }}</label>
                <select name="doctor_id" class="form-select">
                    <option value="">{{ __('messages.allDoctors') }}</option>
                    @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                        {{ $doctor->display_name ?? $doctor->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('messages.statusColumn') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('messages.allStatuses') }}</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>{{ __('messages.scheduled') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                    <option value="no-show" {{ request('status') == 'no-show' ? 'selected' : '' }}>{{ __('messages.no_show') }}</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>{{ __('messages.filter') }}</button>
                <a href="{{ route('reports.appointments') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i>{{ __('messages.clear') }}</a>
            </div>
        </form>
        <hr>
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
        @if(method_exists($appointments, 'links'))
        <div class="mt-3 d-flex justify-content-center">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection