@extends('layouts.dashboard')

@section('title', __('Record Vitals'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">{{ __('Record Vitals') }}</h1>
            <p class="text-muted">{{ __('Patient') }}: {{ $appointment->patient->name }} | {{ __('Date') }}: {{ $appointment->date->format('Y-m-d') }}</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Vitals Form') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('nurse.vitals.store', $appointment) }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="temperature" class="form-label">{{ __('Temperature (°C)') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control @error('temperature') is-invalid @enderror" id="temperature" name="temperature" value="{{ old('temperature') }}" required min="30" max="45">
                                @error('temperature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="pulse" class="form-label">{{ __('Pulse (bpm)') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('pulse') is-invalid @enderror" id="pulse" name="pulse" value="{{ old('pulse') }}" required min="30" max="250">
                                @error('pulse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Blood Pressure (mmHg)') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('bp_systolic') is-invalid @enderror" name="bp_systolic" placeholder="Systolic" value="{{ old('bp_systolic') }}" required min="50" max="250">
                                    <span class="input-group-text">/</span>
                                    <input type="number" class="form-control @error('bp_diastolic') is-invalid @enderror" name="bp_diastolic" placeholder="Diastolic" value="{{ old('bp_diastolic') }}" required min="30" max="150">
                                </div>
                                @if($errors->has('bp_systolic') || $errors->has('bp_diastolic'))
                                    <div class="text-danger small mt-1">{{ $errors->first('bp_systolic') ?: $errors->first('bp_diastolic') }}</div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="respiratory_rate" class="form-label">{{ __('Respiratory Rate (bpm)') }}</label>
                                <input type="number" class="form-control @error('respiratory_rate') is-invalid @enderror" id="respiratory_rate" name="respiratory_rate" value="{{ old('respiratory_rate') }}" min="10" max="60">
                                @error('respiratory_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="weight" class="form-label">{{ __('Weight (kg)') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" value="{{ old('weight') }}" required min="1" max="500">
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                                <input type="number" step="0.01" class="form-control @error('height') is-invalid @enderror" id="height" name="height" value="{{ old('height') }}" min="10" max="300">
                                @error('height')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="oxygen_saturation" class="form-label">{{ __('SpO2 (%)') }}</label>
                                <input type="number" class="form-control @error('oxygen_saturation') is-invalid @enderror" id="oxygen_saturation" name="oxygen_saturation" value="{{ old('oxygen_saturation') }}" min="50" max="100">
                                @error('oxygen_saturation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Medical/Triage Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Save Vitals') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
