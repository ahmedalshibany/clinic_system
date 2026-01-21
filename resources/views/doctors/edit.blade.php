@extends('layouts.dashboard')

@section('title', 'Edit Doctor')
@section('page-title', 'Edit Doctor')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Edit Doctor: {{ $doctor->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('doctors.update', $doctor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        {{-- Personal Information --}}
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2 mb-3">Personal Information</h6>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $doctor->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $doctor->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $doctor->phone) }}" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="specialty" class="form-label">Specialty <span class="text-danger">*</span></label>
                            <select class="form-select @error('specialty') is-invalid @enderror" id="specialty" name="specialty" required>
                                <option value="" disabled>Select Specialty</option>
                                @foreach(['General Practitioner', 'Pediatrics', 'Cardiology', 'Dermatology', 'Orthopedics', 'Dentistry', 'Internal Medicine'] as $spec)
                                    <option value="{{ $spec }}" {{ old('specialty', $doctor->specialty) == $spec ? 'selected' : '' }}>
                                        {{ $spec }}
                                    </option>
                                @endforeach
                            </select>
                            @error('specialty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="bio" class="form-label">Bio / Notes</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3">{{ old('bio', $doctor->bio) }}</textarea>
                            @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Work Information --}}
                        <div class="col-12 mt-4">
                            <h6 class="text-muted border-bottom pb-2 mb-3">Work Information</h6>
                        </div>

                        <div class="col-md-4">
                            <label for="consultation_fee" class="form-label">Consultation Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('consultation_fee') is-invalid @enderror" id="consultation_fee" name="consultation_fee" value="{{ old('consultation_fee', $doctor->consultation_fee) }}" min="0" step="0.01">
                            </div>
                            @error('consultation_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="work_start_time" class="form-label">Work Start Time</label>
                            <input type="time" class="form-control @error('work_start_time') is-invalid @enderror" id="work_start_time" name="work_start_time" value="{{ old('work_start_time', $doctor->work_start_time ? date('H:i', strtotime($doctor->work_start_time)) : '') }}">
                            @error('work_start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="work_end_time" class="form-label">Work End Time</label>
                            <input type="time" class="form-control @error('work_end_time') is-invalid @enderror" id="work_end_time" name="work_end_time" value="{{ old('work_end_time', $doctor->work_end_time ? date('H:i', strtotime($doctor->work_end_time)) : '') }}">
                            @error('work_end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Working Days</label>
                            <div class="btn-group" role="group">
                                @php $workingDays = $doctor->working_days ?? []; @endphp
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <input type="checkbox" class="btn-check" id="day_{{ $day }}" name="working_days[]" value="{{ $day }}" 
                                        {{ in_array($day, old('working_days', $workingDays)) ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="day_{{ $day }}">{{ substr($day, 0, 3) }}</label>
                                @endforeach
                            </div>
                            @error('working_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        
                         <div class="col-12 mt-4">
                             <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $doctor->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Doctor is Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('doctors.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
