@extends('layouts.dashboard')

@section('title', __('messages.appointmentsDetails') . ' - ' . $appointment->patient->name)
@section('page-title', __('messages.appointmentsDetails'))
@section('page-i18n', 'appointmentsDetails')

@section('content')
<div class="mb-4">
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-1"></i> {{ __('messages.dashboard') }}
    </a>
</div>

<div class="row fade-in">
    <div class="col-lg-8 mx-auto">

        {{-- Patient Identity & Appointment Meta --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header" style="background: transparent; border-bottom: 1px solid var(--border-hairline);">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius); background: var(--cream); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-user-injured" style="color: var(--secondary); font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0" style="color: var(--primary);">{{ $appointment->patient->name }}</h5>
                            <small class="text-muted">{{ $appointment->patient->patient_code ?? '' }} · {{ __('messages.phone') }}: {{ $appointment->patient->phone ?? '--' }}</small>
                        </div>
                    </div>
                    @php
                        $statusColors = ['scheduled' => 'secondary', 'pending' => 'warning', 'confirmed' => 'success', 'checked_in' => 'info', 'waiting' => 'warning', 'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger', 'no_show' => 'dark'];
                        $badgeColor = $statusColors[$appointment->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $badgeColor }} px-3 py-2" style="font-size: var(--text-sm);">{{ __("messages.{$appointment->status}") }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="mb-3">
                            <span class="d-block text-muted" style="font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.dateTime') }}</span>
                            <span class="fw-semibold" style="color: var(--primary);">{{ $appointment->date->format('Y-m-d') }} {{ date('h:i A', strtotime($appointment->time)) }}</span>
                        </div>
                        <div class="mb-3">
                            <span class="d-block text-muted" style="font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.type') }}</span>
                            <span class="fw-semibold" style="color: var(--primary);">{{ __("messages.type{$appointment->type}") }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="mb-3">
                            <span class="d-block text-muted" style="font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.doctor') }}</span>
                            <span class="fw-semibold" style="color: var(--primary);">{{ __('messages.doctor_prefix') }} {{ $appointment->doctor->name }}</span>
                            <span class="d-block text-muted" style="font-size: var(--text-xs);">{{ $appointment->doctor->department }}</span>
                        </div>
                        @if($appointment->reason)
                        <div>
                            <span class="d-block text-muted" style="font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.reason') ?? 'Reason' }}</span>
                            <span class="fw-semibold" style="color: var(--primary);">{{ $appointment->reason }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Vitals Shield Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header" style="background: transparent; border-bottom: 1px solid var(--border-hairline);">
                <h6 class="mb-0 fw-bold" style="color: var(--secondary);"><i class="fas fa-heartbeat me-2"></i>{{ __('messages.vitalsSummary') }}</h6>
            </div>
            <div class="card-body">
                @if($appointment->vital)
                <div class="row text-center g-0" style="border-radius: var(--radius); overflow: hidden;">
                    <div class="col-3 py-3" style="background: var(--cream); border-right: 1px solid var(--border-hairline);">
                        <div class="text-muted text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">BP</div>
                        <div class="fw-bold" style="font-size: var(--text-xl); color: var(--primary);">{{ $appointment->vital->blood_pressure }}</div>
                        <div class="text-muted" style="font-size: var(--text-xs);">mmHg</div>
                    </div>
                    <div class="col-3 py-3" style="background: var(--cream); border-right: 1px solid var(--border-hairline);">
                        <div class="text-muted text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">{{ __('messages.doctor_pulse') }}</div>
                        <div class="fw-bold" style="font-size: var(--text-xl); color: var(--primary);">{{ $appointment->vital->pulse ?? '--' }}</div>
                        <div class="text-muted" style="font-size: var(--text-xs);">bpm</div>
                    </div>
                    <div class="col-3 py-3" style="background: var(--cream); border-right: 1px solid var(--border-hairline);">
                        <div class="text-muted text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">{{ __('messages.doctor_temp') }}</div>
                        <div class="fw-bold" style="font-size: var(--text-xl); color: var(--primary);">{{ $appointment->vital->temperature ?? '--' }}</div>
                        <div class="text-muted" style="font-size: var(--text-xs);">°C</div>
                    </div>
                    <div class="col-3 py-3" style="background: var(--cream);">
                        <div class="text-muted text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">{{ __('messages.doctor_bmi') }}</div>
                        <div class="fw-bold" style="font-size: var(--text-xl); color: var(--primary);">{{ $appointment->vital->bmi ?? '--' }}</div>
                        @if($appointment->vital->bmi_category)
                        <div class="text-muted" style="font-size: var(--text-xs);">{{ ['Underweight' => __('messages.bmi_underweight'), 'Normal' => __('messages.bmi_normal'), 'Overweight' => __('messages.bmi_overweight'), 'Obese' => __('messages.bmi_obese')][$appointment->vital->bmi_category] ?? $appointment->vital->bmi_category }}</div>
                        @endif
                    </div>
                </div>

                @if($appointment->vital->respiratory_rate || $appointment->vital->oxygen_saturation)
                <div class="row text-center g-0 mt-1" style="border-radius: var(--radius); overflow: hidden;">
                    @if($appointment->vital->respiratory_rate)
                    <div class="col-6 py-3" style="background: var(--cream); border-right: 1px solid var(--border-hairline);">
                        <div class="text-muted text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">RR</div>
                        <div class="fw-semibold" style="font-size: var(--text-lg); color: var(--primary);">{{ $appointment->vital->respiratory_rate }}</div>
                        <div class="text-muted" style="font-size: var(--text-xs);">brpm</div>
                    </div>
                    @endif
                    @if($appointment->vital->oxygen_saturation)
                    <div class="col-6 py-3" style="background: var(--cream);">
                        <div class="text-muted text-uppercase" style="font-size: var(--text-xs); letter-spacing: 0.05em;">SpO₂</div>
                        <div class="fw-semibold" style="font-size: var(--text-lg); color: var(--primary);">{{ $appointment->vital->oxygen_saturation }}%</div>
                        <div class="text-muted" style="font-size: var(--text-xs);">%</div>
                    </div>
                    @endif
                </div>
                @endif

                @if($appointment->vital->notes)
                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-hairline);">
                    <span class="d-block text-muted mb-1" style="font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.notesColon') }}</span>
                    <p class="mb-0" style="font-size: var(--text-sm); color: var(--text-primary);">{{ $appointment->vital->notes }}</p>
                </div>
                @endif

                @if($appointment->vital->weight || $appointment->vital->height)
                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-hairline);">
                    <div class="row g-3">
                        @if($appointment->vital->weight)
                        <div class="col-auto">
                            <span class="text-muted" style="font-size: var(--text-xs);">{{ __('messages.weight') }}:</span>
                            <span class="fw-semibold" style="color: var(--primary); font-size: var(--text-sm);">{{ $appointment->vital->weight }} kg</span>
                        </div>
                        @endif
                        @if($appointment->vital->height)
                        <div class="col-auto">
                            <span class="text-muted" style="font-size: var(--text-xs);">{{ __('messages.height') }}:</span>
                            <span class="fw-semibold" style="color: var(--primary); font-size: var(--text-sm);">{{ $appointment->vital->height }} cm</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                @else
                <div class="text-center py-4">
                    <i class="fas fa-heartbeat text-muted mb-2" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    <p class="text-muted mb-0">{{ __('messages.vitalsNotRecorded') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Diagnosis, Notes & Prescription --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background: transparent; border-bottom: 1px solid var(--border-hairline);">
                <h6 class="mb-0 fw-bold" style="color: var(--secondary);"><i class="fas fa-prescription me-2"></i>{{ __('messages.diagnosis_notes') }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <span class="d-block text-muted mb-2" style="font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.diagnosis') }}</span>
                        <div class="p-3 rounded" style="background: var(--cream); border: 1px solid var(--border-hairline); min-height: 100px; color: var(--text-primary); font-size: var(--text-sm);">
                            {{ $appointment->diagnosis ?? __('messages.noNotesAvailable') }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span class="d-block text-muted mb-2" style="font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.notes') }}</span>
                        <div class="p-3 rounded" style="background: var(--cream); border: 1px solid var(--border-hairline); min-height: 100px; color: var(--text-primary); font-size: var(--text-sm);">
                            {{ $appointment->notes ?? __('messages.noNotesAvailable') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
