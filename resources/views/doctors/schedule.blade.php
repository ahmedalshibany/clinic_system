@extends('layouts.dashboard')

@section('title', __('messages.manageSchedule') . ' - ' . $doctor->name)
@section('page-title', __('messages.manageSchedule'))
@section('page-i18n', 'schedule')

@section('content')
<a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary mb-3">
    <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-1"></i> {{ __('messages.backToDoctors') }}
</a>

<div class="card mb-4 fade-in border-0 shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>{{ __('messages.manageSchedule') }}: {{ $doctor->name }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('doctors.schedule.update', $doctor) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px" data-i18n="day">{{ __('messages.day') }}</th>
                            <th data-i18n="status">{{ __('messages.status') }}</th>
                            <th data-i18n="startTime">{{ __('messages.workStartTime') }}</th>
                            <th data-i18n="endTime">{{ __('messages.workEndTime') }}</th>
                            <th data-i18n="slotDuration">{{ __('messages.slotDuration') }}</th>
                            <th data-i18n="maxPatients">{{ __('messages.maxPatients') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $dayKeys = ['day_sunday', 'day_monday', 'day_tuesday', 'day_wednesday', 'day_thursday', 'day_friday', 'day_saturday'];
                        @endphp

                        @foreach($dayKeys as $index => $key)
                            @php
                                $schedule = $schedules[$index] ?? null;
                            @endphp
                            <tr>
                                <td class="fw-bold">
                                    {{ __('messages.' . $key) }}
                                    <input type="hidden" name="schedules[{{ $index }}][day_of_week]" value="{{ $index }}">
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="schedules[{{ $index }}][is_active]" value="1"
                                            {{ ($schedule && $schedule->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" data-i18n="active">{{ __('messages.active') }}</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="time" class="form-control" name="schedules[{{ $index }}][start_time]"
                                        value="{{ $schedule ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '09:00' }}">
                                </td>
                                <td>
                                    <input type="time" class="form-control" name="schedules[{{ $index }}][end_time]"
                                        value="{{ $schedule ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '17:00' }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="schedules[{{ $index }}][slot_duration]"
                                        value="{{ $schedule->slot_duration ?? 30 }}" min="5" max="120" step="5">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="schedules[{{ $index }}][max_appointments]"
                                        value="{{ $schedule->max_appointments ?? '' }}" placeholder="{{ __('messages.unlimited') }}" min="1">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> {{ __('messages.save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4 fade-in border-0 shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-plane me-2"></i>{{ __('messages.leaveManagement') }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-7">
                <h6 class="mb-3" data-i18n="upcomingLeaves">{{ __('messages.upcomingLeaves') }}</h6>
                @if($leaves->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th data-i18n="from">{{ __('messages.from') }}</th>
                                    <th data-i18n="to">{{ __('messages.to') }}</th>
                                    <th data-i18n="reason">{{ __('messages.reason') }}</th>
                                    <th data-i18n="actions">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaves as $leave)
                                    <tr>
                                        <td>{{ $leave->start_date->format('M d, Y') }}</td>
                                        <td>{{ $leave->end_date->format('M d, Y') }}</td>
                                        <td>{{ $leave->reason ?? '-' }}</td>
                                        <td>
                                            <form action="{{ route('doctors.leaves.destroy', [$doctor, $leave]) }}" method="POST" onsubmit="return confirm(window.translations[document.documentElement.lang || 'en'].confirmDeleteLeave)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted" data-i18n="noUpcomingLeaves">{{ __('messages.noUpcomingLeaves') }}</p>
                @endif
            </div>

            <div class="col-md-5">
                <div class="bg-light p-3 rounded">
                    <h6 class="mb-3" data-i18n="addNewLeave">{{ __('messages.addNewLeave') }}</h6>
                    <form action="{{ route('doctors.leaves.store', $doctor) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" data-i18n="startDate">{{ __('messages.startDate') }}</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-i18n="endDate">{{ __('messages.endDate') }}</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date') }}" required min="{{ date('Y-m-d') }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-i18n="reasonOptional">{{ __('messages.reasonOptional') }}</label>
                            <input type="text" class="form-control @error('reason') is-invalid @enderror" name="reason" value="{{ old('reason') }}" placeholder="{{ __('messages.placeholderLeaveReason') }}">
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> <span data-i18n="addLeave">{{ __('messages.addLeave') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
