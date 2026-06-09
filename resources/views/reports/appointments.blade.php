@extends('layouts.dashboard')
@section('title', __('messages.appointments_report'))
@section('page-title', __('messages.appointments_report'))
@section('page-i18n', 'appointments_report')
@section('content')

<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mb-3">
    <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-1"></i> {{ __('messages.backToReports') }}
</a>

<!-- Action Toolbar -->
<div class="action-toolbar d-flex gap-3 flex-wrap align-items-center justify-content-between mb-4 fade-in">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="date" name="date_from" class="form-control" style="width: auto;" value="{{ request('date_from') }}">
        <input type="date" name="date_to" class="form-control" style="width: auto;" value="{{ request('date_to') }}">
        <select name="doctor_id" class="form-select" style="width: auto;">
            <option value="">{{ __('messages.allDoctors') }}</option>
            @foreach($doctors as $doctor)
            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                {{ $doctor->display_name ?? $doctor->name }}
            </option>
            @endforeach
        </select>
        <select name="status" class="form-select" style="width: auto;">
            <option value="">{{ __('messages.allStatuses') }}</option>
            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>{{ __('messages.scheduled') }}</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
            <option value="no-show" {{ request('status') == 'no-show' ? 'selected' : '' }}>{{ __('messages.no_show') }}</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1"><i class="fas fa-filter"></i>{{ __('messages.filter') }}</button>
        <a href="{{ route('reports.appointments') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"><i class="fas fa-times"></i>{{ __('messages.clear') }}</a>
    </form>
</div>

<!-- Status Stats -->
<div class="row g-2 mb-4">
    @forelse($status_stats as $status => $count)
    <div class="col-md-2 col-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <h4 class="fw-bold mb-0" style="color: var(--secondary);">{{ $count }}</h4>
            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">{{ __('messages.' . str_replace('-', '_', $status)) }}</small>
        </div>
    </div>
    @empty
    <div class="col-12 text-center text-muted py-4">{{ __('messages.noTransactions') }}</div>
    @endforelse
</div>

<!-- Appointments Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">{{ __('messages.dateColumn') }}</th>
                        <th class="py-3">{{ __('messages.patientColumn') }}</th>
                        <th class="py-3">{{ __('messages.doctorColumn') }}</th>
                        <th class="pe-4 py-3">{{ __('messages.statusColumn') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                    <tr>
                        <td class="ps-4">{{ $appt->date->format('M d, Y') }} {{ $appt->time->format('H:i') }}</td>
                        <td><span class="fw-medium">{{ $appt->patient->name }}</span></td>
                        <td>{{ $appt->doctor->user->name }}</td>
                        <td class="pe-4">
                            @php
                                $statusColors = [
                                    'scheduled' => 'primary',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger',
                                    'no-show' => 'secondary',
                                    'no_show' => 'secondary',
                                ];
                                $badgeColor = $statusColors[$appt->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} px-3 py-2 rounded-pill">
                                {{ __('messages.' . str_replace('-', '_', $appt->status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">{{ __('messages.noTransactions') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($appointments, 'links') && $appointments->hasPages())
        <div class="pagination-controls">
            <div class="pagination-info">
                {{ __('messages.showing') }} <strong>{{ $appointments->firstItem() }}-{{ $appointments->lastItem() }}</strong> {{ __('messages.of') }} <strong>{{ $appointments->total() }}</strong> {{ __('messages.resultsLabel') }}
            </div>
            <div class="d-flex gap-2">
                @if($appointments->onFirstPage())
                    <button class="btn btn-light btn-sm" disabled><i class="fas fa-chevron-left"></i> {{ __('messages.previous') }}</button>
                @else
                    <a href="{{ $appointments->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> {{ __('messages.previous') }}</a>
                @endif
                @if($appointments->hasMorePages())
                    <a href="{{ $appointments->nextPageUrl() }}" class="btn btn-light btn-sm">{{ __('messages.next') }} <i class="fas fa-chevron-right"></i></a>
                @else
                    <button class="btn btn-light btn-sm" disabled>{{ __('messages.next') }} <i class="fas fa-chevron-right"></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
