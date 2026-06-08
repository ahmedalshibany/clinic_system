@extends('layouts.dashboard')

@section('title', $doctor->name . ' - ' . __('messages.doctorProfile'))
@section('page-title', __('messages.doctorProfile'))
@section('page-i18n', 'doctors')

@section('content')
<a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary mb-3">
    <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-1"></i> {{ __('messages.backToDoctors') }}
</a>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 fade-in">
            <div class="card-body text-center p-4">
                <div class="doctor-avatar mb-3">
                    @if($doctor->avatar)
                        <img src="{{ $doctor->avatar }}" alt="{{ $doctor->name }}" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                        <div class="avatar-circle bg-primary-soft text-primary mx-auto d-flex align-items-center justify-content-center rounded-circle" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            {{ substr($doctor->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <h5 class="mb-1">{{ $doctor->name }}</h5>
                <p class="text-muted mb-1">{{ $doctor->specialty }}</p>
                @if($doctor->department)
                    <p class="text-muted small mb-2"><i class="fas fa-building me-1"></i>{{ $doctor->department }}</p>
                @endif
                <span class="badge {{ $doctor->is_active ? 'bg-success' : 'bg-danger' }} mb-4">
                    {{ $doctor->is_active ? __('messages.active') : __('messages.inactive') }}
                </span>

                <div class="d-grid gap-2">
                    <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i> <span data-i18n="editProfile">{{ __('messages.editProfile') }}</span>
                    </a>
                </div>
            </div>
            <div class="card-footer p-4">
                <h6 class="text-uppercase text-muted small fw-bold mb-3" data-i18n="contactInfo">{{ __('messages.contactInfo') }}</h6>
                <div class="d-flex align-items-center mb-3">
                    <div class="text-primary me-3"><i class="fas fa-phone"></i></div>
                    <div>{{ $doctor->phone }}</div>
                </div>
                @if($doctor->email)
                <div class="d-flex align-items-center mb-3">
                    <div class="text-primary me-3"><i class="fas fa-envelope"></i></div>
                    <div>{{ $doctor->email }}</div>
                </div>
                @endif

                <h6 class="text-uppercase text-muted small fw-bold mb-3 mt-4" data-i18n="practiceInfo">{{ __('messages.practiceInfo') }}</h6>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted" data-i18n="feeLabel">{{ __('messages.feeLabel') }}</span>
                    <span class="fw-bold">${{ number_format($doctor->consultation_fee, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted" data-i18n="patientsLabel">{{ __('messages.patients') }}:</span>
                    <span class="fw-bold">{{ $doctor->appointments()->distinct('patient_id')->count('patient_id') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card border-0 shadow-sm fade-in">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs m-0" id="doctorTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active border-0 border-bottom border-primary py-3 px-4" id="bio-tab" data-bs-toggle="tab" data-bs-target="#bio" type="button" role="tab" data-i18n="overview">{{ __('messages.overview') }}</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link border-0 py-3 px-4" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab" data-i18n="appointments">{{ __('messages.appointments') }}</button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="doctorTabsContent">
                    <div class="tab-pane fade show active" id="bio" role="tabpanel">
                        <h6 class="fw-bold mb-3" data-i18n="biography">{{ __('messages.biography') }}</h6>
                        <p class="text-muted mb-4">{{ $doctor->bio ?? __('messages.noBiography') }}</p>

                        <h6 class="fw-bold mb-3" data-i18n="workingHours">{{ __('messages.workingHours') }}</h6>
                        @php
                            $dayKeys = ['day_sunday', 'day_monday', 'day_tuesday', 'day_wednesday', 'day_thursday', 'day_friday', 'day_saturday'];
                            $scheduleMatrix = $doctor->schedules()->get()->keyBy('day_of_week');
                        @endphp
                        <div class="d-flex flex-column gap-2 mb-4">
                            @foreach($dayKeys as $i => $key)
                                @php $sched = $scheduleMatrix->get($i); @endphp
                                <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded-3 {{ $sched && $sched->is_active ? 'bg-primary-soft' : 'bg-light' }}">
                                    <span class="fw-medium">{{ __('messages.' . $key) }}</span>
                                    @if($sched && $sched->is_active)
                                        <span class="text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($sched->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('h:i A') }}
                                            @if($sched->slot_duration)
                                                <span class="ms-2 badge bg-info text-white">{{ $sched->slot_duration }} {{ __('messages.minutesAbbr') }}</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted small fst-italic" data-i18n="dayOff">{{ __('messages.dayOff') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($doctor->work_start_time && $doctor->work_end_time)
                            <div class="alert alert-info py-2 px-3 d-inline-block">
                                <i class="fas fa-clock me-2"></i>
                                <span data-i18n="shiftsLabel">{{ __('messages.shiftsLabel') }}</span> {{ date('h:i A', strtotime($doctor->work_start_time)) }} - {{ date('h:i A', strtotime($doctor->work_end_time)) }}
                            </div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="appointments" role="tabpanel">
                        <h6 class="fw-bold mb-3" data-i18n="recentAppointments">{{ __('messages.recentAppointments') }}</h6>
                        @if($doctor->appointments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="">
                                        <tr>
                                            <th data-i18n="date">{{ __('messages.date') }}</th>
                                            <th data-i18n="patient">{{ __('messages.patient') }}</th>
                                            <th data-i18n="status">{{ __('messages.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($doctor->appointments->sortByDesc('date')->take(10) as $appt)
                                            <tr>
                                                <td>
                                                    {{ $appt->date->format('M d, Y') }}<br>
                                                    <small class="text-muted">{{ $appt->time->format('h:i A') }}</small>
                                                </td>
                                                <td>{{ $appt->patient->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $appt->status == 'completed' ? 'success' : ($appt->status == 'confirmed' ? 'primary' : 'secondary') }}">
                                                        {{ ucfirst($appt->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fa-3x opacity-50 mb-3"></i>
                                <p data-i18n="noAppointments">{{ __('messages.noAppointments') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
