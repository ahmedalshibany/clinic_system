@extends('layouts.app')

@section('title', __('Appointment Details'))

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between">
                <h6>{{ __('Appointment Details') }}</h6>
                <div>
                   <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-sm btn-info">{{ __('Edit') }}</a>
                   <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-secondary">{{ __('Back') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('Date & Time') }}</div>
                    <div class="col-sm-8 fw-bold">
                        {{ $appointment->date->format('Y-m-d') }} {{ date('h:i A', strtotime($appointment->time)) }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('Patient') }}</div>
                    <div class="col-sm-8">
                        <a href="{{ route('patients.show', $appointment->patient_id) }}" class="text-primary text-decoration-none">
                            {{ $appointment->patient->name }}
                        </a>
                        <br>
                        <small class="text-muted">{{ __('Phone') }}: {{ $appointment->patient->phone }}</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('Doctor') }}</div>
                    <div class="col-sm-8">
                        <span class="fw-bold">{{ $appointment->doctor->name }}</span>
                        <br>
                        <small class="text-muted">{{ $appointment->doctor->department }}</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('Status') }}</div>
                    <div class="col-sm-8">
                        @if($appointment->status == 'confirmed')
                            <span class="badge bg-success">{{ __('Confirmed') }}</span>
                        @elseif($appointment->status == 'pending')
                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                        @elseif($appointment->status == 'cancelled')
                            <span class="badge bg-danger">{{ __('Cancelled') }}</span>
                        @elseif($appointment->status == 'completed')
                            <span class="badge bg-info">{{ __('Completed') }}</span>
                        @endif
                    </div>
                </div>
                
                 <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('Type') }}</div>
                    <div class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $appointment->type)) }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary">{{ __('Notes') }}</div>
                    <div class="col-sm-8">
                        <p class="mb-0">{{ $appointment->notes ?? __('No notes available.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
