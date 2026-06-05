@extends('layouts.dashboard')

@section('title', __('messages.appointmentsDetails'))
@section('page-title', __('messages.appointmentsDetails'))

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0">{{ __('messages.appointmentsDetails') }}</h6>
                <div class="d-flex gap-2 flex-wrap">
                    @if(!$appointment->vital && $appointment->status != 'cancelled')
                        <a href="{{ route('nurse.vitals.create', $appointment) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-heartbeat me-1"></i> {{ __('messages.recordVitals') }}
                        </a>
                    @endif

                   @if($appointment->status == 'pending' || $appointment->status == 'confirmed')
                        <form action="{{ route('appointments.no-show', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirmNoShow') }}')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-user-slash me-1"></i> {{ __('messages.markNoShow') }}
                            </button>
                        </form>
                    @endif

                   <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-sm btn-info">{{ __('messages.edit') }}</a>
                   @php
                       $backRoute = match(request('from')) {
                           'dashboard' => route('dashboard'),
                           'calendar' => route('appointments.calendar'),
                           default => route('appointments.index'),
                       };
                   @endphp
                   <a href="{{ $backRoute }}" class="btn btn-sm btn-secondary">{{ __('messages.back') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('messages.dateTime') }}</div>
                    <div class="col-sm-8 fw-bold">
                        {{ $appointment->date->format('Y-m-d') }} {{ date('h:i A', strtotime($appointment->time)) }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('messages.patient') }}</div>
                    <div class="col-sm-8">
                        <a href="{{ route('patients.show', $appointment->patient_id) }}" class="text-primary text-decoration-none">
                            {{ $appointment->patient->name }}
                        </a>
                        <br>
                        <small class="text-muted">{{ __('messages.phone') }}: {{ $appointment->patient->phone }}</small>
                    </div>
                </div>

                @if($appointment->vital)
                <div class="card border-info mb-3">
                    <div class="card-header bg-info-subtle text-info-emphasis fw-bold">
                        <i class="fas fa-heartbeat me-1"></i> {{ __('messages.vitalsSummary') }}
                    </div>
                    <div class="card-body py-3">
                        <div class="row text-center">
                            <div class="col-3 border-end">
                                <small class="text-secondary d-block text-uppercase" style="font-size: 0.75rem;">BP</small>
                                <span class="fw-bold fs-5">{{ $appointment->vital->blood_pressure }}</span>
                                <small class="text-muted d-block">mmHg</small>
                            </div>
                            <div class="col-3 border-end">
                                <small class="text-secondary d-block text-uppercase" style="font-size: 0.75rem;">Pulse</small>
                                <span class="fw-bold fs-5">{{ $appointment->vital->pulse }}</span>
                                <small class="text-muted d-block">bpm</small>
                            </div>
                            <div class="col-3 border-end">
                                <small class="text-secondary d-block text-uppercase" style="font-size: 0.75rem;">Temp</small>
                                <span class="fw-bold fs-5">{{ $appointment->vital->temperature }}</span>
                                <small class="text-muted d-block">°C</small>
                            </div>
                            <div class="col-3">
                                <small class="text-secondary d-block text-uppercase" style="font-size: 0.75rem;">Weight</small>
                                <span class="fw-bold fs-5">{{ $appointment->vital->weight }}</span>
                                <small class="text-muted d-block">kg</small>
                            </div>
                        </div>
                        @if($appointment->vital->notes)
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted fw-bold">{{ __('messages.notesColon') }}</small>
                            <p class="mb-0 small fst-italic">{{ $appointment->vital->notes }}</p>
                        </div>
                        @endif
                    </div>
                    @if(in_array(auth()->user()->role, ['admin', 'doctor']) && !in_array($appointment->status, ['pending', 'cancelled']))
                    <div class="card-footer border-top d-flex justify-content-end">
                        <form action="{{ route('appointments.reopen-vitals', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.reopenVitalsConfirm') }}')">
                            @csrf
                            <button type="submit" class="btn btn-warning fw-bold px-4">
                                <i class="fas fa-unlock me-2"></i> {{ __('messages.reopenVitalsFull') }}
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @elseif(in_array(auth()->user()->role, ['admin', 'doctor']) && !in_array($appointment->status, ['pending', 'cancelled']))
                <div class="alert alert-warning d-flex align-items-center gap-3 mb-3">
                    <i class="fas fa-info-circle fa-lg"></i>
                    <div class="flex-grow-1">
                        <strong>{{ __('messages.noVitalsRecorded') }}</strong>
                        <span class="ms-1">{{ __('messages.thisAppointmentIs') }} <strong>{{ $appointment->status }}</strong>.</span>
                    </div>
                    <form action="{{ route('appointments.reopen-vitals', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.reopenVitalsConfirm') }}')">
                        @csrf
                        <button type="submit" class="btn btn-warning fw-bold btn-sm">
                            <i class="fas fa-unlock me-1"></i> {{ __('messages.reopenVitals') }}
                        </button>
                    </form>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('messages.doctor') }}</div>
                    <div class="col-sm-8">
                        <span class="fw-bold">{{ $appointment->doctor->name }}</span>
                        <br>
                        <small class="text-muted">{{ $appointment->doctor->department }}</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('messages.status') }}</div>
                    <div class="col-sm-8">
                        @if($appointment->status == 'confirmed')
                            <span class="badge bg-success">{{ __('messages.confirmed') }}</span>
                        @elseif($appointment->status == 'pending')
                            <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                        @elseif($appointment->status == 'cancelled')
                            <span class="badge bg-danger">{{ __('messages.cancelled') }}</span>
                        @elseif($appointment->status == 'completed')
                            <span class="badge bg-info">{{ __('messages.completed') }}</span>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'doctor']) && !in_array($appointment->status, ['pending', 'cancelled']))
                            <form action="{{ route('appointments.reopen-vitals', $appointment) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('{{ __('messages.reopenVitalsConfirm') }}')">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">
                                    <i class="fas fa-unlock me-1"></i> {{ __('messages.reopenVitals') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                
                 <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('messages.type') }}</div>
                    <div class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $appointment->type)) }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('messages.notes') }}</div>
                    <div class="col-sm-8">
                        <p class="mb-0">{{ $appointment->notes ?? __('messages.noNotesAvailable') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
