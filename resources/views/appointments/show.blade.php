@extends('layouts.dashboard')

@section('title', __('messages.appointmentsDetails'))
@section('page-title', __('messages.appointmentsDetails'))

@section('content')
<div class="mb-3">
    <a href="{{ url()->previous() && url()->previous() !== url()->current() ? url()->previous() : route('appointments.index') }}" class="btn btn-sm btn-light">
        <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back') }}
    </a>
</div>
<div class="row fade-in">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
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
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-4 text-secondary">{{ __('messages.dateTime') }}</div>
                    <div class="col-sm-8 fw-bold">
                        {{ $appointment->date->format('Y-m-d') }} {{ date('h:i A', strtotime($appointment->time)) }}
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-4 text-secondary">{{ __('messages.patient') }}</div>
                    <div class="col-sm-8">
                        <a href="{{ route('patients.show', $appointment->patient_id) }}" class="fw-bold text-decoration-none" style="color: var(--secondary);">
                            {{ $appointment->patient->name }}
                        </a>
                        <div class="text-secondary" style="font-size: var(--text-sm);">{{ __('messages.phone') }}: {{ $appointment->patient->phone }}</div>
                    </div>
                </div>

                @if($appointment->vital)
                <div class="mb-4" style="background: rgba(61, 90, 128, 0.06); border: 1px solid rgba(61, 90, 128, 0.15); border-radius: var(--radius-lg); overflow: hidden;">
                    <div class="px-4 py-3 fw-bold" style="color: var(--info); border-bottom: 1px solid rgba(61, 90, 128, 0.1);">
                        <i class="fas fa-heartbeat me-2"></i>{{ __('messages.vitalsSummary') }}
                    </div>
                    <div class="p-4">
                        <div class="row text-center g-0">
                            <div class="col-3">
                                <div class="text-secondary text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">BP</div>
                                <div class="fw-bold" style="font-size: var(--text-xl); color: var(--primary);">{{ $appointment->vital->blood_pressure }}</div>
                                <div class="text-secondary" style="font-size: var(--text-xs);">mmHg</div>
                            </div>
                            <div class="col-3">
                                <div class="text-secondary text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">Pulse</div>
                                <div class="fw-bold" style="font-size: var(--text-xl); color: var(--primary);">{{ $appointment->vital->pulse }}</div>
                                <div class="text-secondary" style="font-size: var(--text-xs);">bpm</div>
                            </div>
                            <div class="col-3">
                                <div class="text-secondary text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">Temp</div>
                                <div class="fw-bold" style="font-size: var(--text-xl); color: var(--primary);">{{ $appointment->vital->temperature }}</div>
                                <div class="text-secondary" style="font-size: var(--text-xs);">°C</div>
                            </div>
                            <div class="col-3">
                                <div class="text-secondary text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">Weight</div>
                                <div class="fw-bold" style="font-size: var(--text-xl); color: var(--primary);">{{ $appointment->vital->weight }}</div>
                                <div class="text-secondary" style="font-size: var(--text-xs);">kg</div>
                            </div>
                        </div>
                        @if($appointment->vital->notes)
                        <div class="mt-4 pt-3" style="border-top: 1px solid var(--border-light);">
                            <div class="fw-bold mb-1 text-secondary" style="font-size: var(--text-sm);">{{ __('messages.notesColon') }}</div>
                            <p class="mb-0" style="font-size: var(--text-sm); color: var(--text-primary);">{{ $appointment->vital->notes }}</p>
                        </div>
                        @endif
                    </div>
                    @if(in_array(auth()->user()->role, ['admin', 'doctor']) && !in_array($appointment->status, ['pending', 'cancelled']))
                    <div class="px-4 py-3 d-flex justify-content-end" style="border-top: 1px solid var(--border-light);">
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
                <div class="d-flex align-items-center gap-3 mb-4 p-3" style="background: rgba(191, 140, 48, 0.08); border: 1px solid rgba(191, 140, 48, 0.2); border-radius: var(--radius);">
                    <i class="fas fa-info-circle fa-lg" style="color: var(--warning);"></i>
                    <div class="flex-grow-1">
                        <strong style="color: var(--warning);">{{ __('messages.noVitalsRecorded') }}</strong>
                        <span class="ms-1 text-secondary">{{ __('messages.thisAppointmentIs') }} <strong>{{ $appointment->status }}</strong>.</span>
                    </div>
                    <form action="{{ route('appointments.reopen-vitals', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.reopenVitalsConfirm') }}')">
                        @csrf
                        <button type="submit" class="btn btn-warning fw-bold btn-sm">
                            <i class="fas fa-unlock me-1"></i> {{ __('messages.reopenVitals') }}
                        </button>
                    </form>
                </div>
                @endif

                <div class="row mb-4">
                    <div class="col-sm-4 text-secondary">{{ __('messages.doctor') }}</div>
                    <div class="col-sm-8">
                        <span class="fw-bold">{{ $appointment->doctor->name }}</span>
                        <div class="text-secondary" style="font-size: var(--text-sm);">{{ $appointment->doctor->department }}</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-4 text-secondary">{{ __('messages.status') }}</div>
                    <div class="col-sm-8">
                        @php
                            $statusColors = ['confirmed' => 'success', 'pending' => 'warning', 'cancelled' => 'danger', 'completed' => 'info'];
                            $badgeColor = $statusColors[$appointment->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $badgeColor }} px-3 py-2">{{ __("messages.{$appointment->status}") }}</span>

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
                
                <div class="row mb-4">
                    <div class="col-sm-4 text-secondary">{{ __('messages.type') }}</div>
                    <div class="col-sm-8 fw-medium">{{ ucfirst(str_replace('_', ' ', $appointment->type)) }}</div>
                </div>

                <div class="row">
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
