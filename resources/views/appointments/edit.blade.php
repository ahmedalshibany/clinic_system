@extends('layouts.dashboard')

@section('title', __('Edit Appointment'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0 border-0">
                <h6 class="fw-bold"><i class="fas fa-edit me-2"></i>{{ __('Edit Appointment') }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('appointments.update', $appointment) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mt-3">
                        <!-- Patient Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="patient_id" class="form-label fw-bold">{{ __('Patient') }} <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select Patient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }} (ID: {{ $patient->patient_code ?? $patient->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Doctor Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="doctor_id" class="form-label fw-bold">{{ __('Doctor') }} <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select Doctor') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }} - {{ $doctor->department ?? $doctor->specialty }}
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
                            <label for="date" class="form-label fw-bold">{{ __('Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $appointment->date->format('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Time -->
                        <div class="col-md-6 mb-3">
                            <label for="time" class="form-label fw-bold">{{ __('Time') }} <span class="text-danger">*</span></label>
                            <input type="time" name="time" id="time" class="form-control @error('time') is-invalid @enderror" value="{{ old('time', $appointment->time->format('H:i')) }}" required>
                            @error('time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-bold">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                                <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                                <option value="waiting" {{ old('status', $appointment->status) == 'waiting' ? 'selected' : '' }}>{{ __('Waiting') }}</option>
                                <option value="in_progress" {{ old('status', $appointment->status) == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                <option value="no_show" {{ old('status', $appointment->status) == 'no_show' ? 'selected' : '' }}>{{ __('No Show') }}</option>
                                <option value="checked_in" {{ old('status', $appointment->status) == 'checked_in' ? 'selected' : '' }}>{{ __('Checked In') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label fw-bold">{{ __('Type') }} <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="Checkup" {{ old('type', $appointment->type) == 'Checkup' ? 'selected' : '' }}>{{ __('Checkup') }}</option>
                                <option value="Consultation" {{ old('type', $appointment->type) == 'Consultation' ? 'selected' : '' }}>{{ __('Consultation') }}</option>
                                <option value="Follow-up" {{ old('type', $appointment->type) == 'Follow-up' ? 'selected' : '' }}>{{ __('Follow-up') }}</option>
                                <option value="Emergency" {{ old('type', $appointment->type) == 'Emergency' ? 'selected' : '' }}>{{ __('Emergency') }}</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-bold">{{ __('Notes') }}</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Additional details...">{{ old('notes', $appointment->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mb-2">
                        <a href="{{ url()->previous() == request()->url() ? route('appointments.index') : url()->previous() }}" class="btn btn-light px-4">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>{{ __('Update Appointment') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
