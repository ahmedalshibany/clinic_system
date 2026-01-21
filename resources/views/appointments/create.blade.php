@extends('layouts.app')

@section('title', __('Add Appointment'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6>{{ __('Add New Appointment') }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <!-- Patient Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="patient_id" class="form-label">{{ __('Patient') }}</label>
                            <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select Patient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }} (ID: {{ $patient->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Doctor Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="doctor_id" class="form-label">{{ __('Doctor') }}</label>
                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select Doctor') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }} - {{ $doctor->department }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Date -->
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">{{ __('Date') }}</label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Time -->
                        <div class="col-md-6 mb-3">
                            <label for="time" class="form-label">{{ __('Time') }}</label>
                            <input type="time" name="time" id="time" class="form-control @error('time') is-invalid @enderror" value="{{ old('time') }}" required>
                            @error('time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">{{ __('Type') }}</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="Checkup" {{ old('type') == 'Checkup' ? 'selected' : '' }}>{{ __('Checkup') }}</option>
                                <option value="Consultation" {{ old('type') == 'Consultation' ? 'selected' : '' }}>{{ __('Consultation') }}</option>
                                <option value="Follow-up" {{ old('type') == 'Follow-up' ? 'selected' : '' }}>{{ __('Follow-up') }}</option>
                                <option value="Emergency" {{ old('type') == 'Emergency' ? 'selected' : '' }}>{{ __('Emergency') }}</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary me-2">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Create Appointment') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
