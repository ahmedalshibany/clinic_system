@extends('layouts.dashboard')

@section('title', 'Add Doctor')
@section('page-title', 'Add Doctor')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">New Doctor</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('doctors.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3">
                        {{-- Personal Information --}}
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2 mb-3">Personal Information</h6>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="specialty" class="form-label">Specialty <span class="text-danger">*</span></label>
                            <select class="form-select @error('specialty') is-invalid @enderror" id="specialty" name="specialty" required>
                                <option value="" selected disabled>Select Specialty</option>
                                <option value="General Practitioner" {{ old('specialty') == 'General Practitioner' ? 'selected' : '' }}>General Practitioner</option>
                                <option value="Pediatrics" {{ old('specialty') == 'Pediatrics' ? 'selected' : '' }}>Pediatrics</option>
                                <option value="Cardiology" {{ old('specialty') == 'Cardiology' ? 'selected' : '' }}>Cardiology</option>
                                <option value="Dermatology" {{ old('specialty') == 'Dermatology' ? 'selected' : '' }}>Dermatology</option>
                                <option value="Orthopedics" {{ old('specialty') == 'Orthopedics' ? 'selected' : '' }}>Orthopedics</option>
                                <option value="Dentistry" {{ old('specialty') == 'Dentistry' ? 'selected' : '' }}>Dentistry</option>
                                <option value="Internal Medicine" {{ old('specialty') == 'Internal Medicine' ? 'selected' : '' }}>Internal Medicine</option>
                            </select>
                            @error('specialty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="bio" class="form-label">Bio / Notes</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3">{{ old('bio') }}</textarea>
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
                                <input type="number" class="form-control @error('consultation_fee') is-invalid @enderror" id="consultation_fee" name="consultation_fee" value="{{ old('consultation_fee') }}" min="0" step="0.01">
                            </div>
                            @error('consultation_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="work_start_time" class="form-label">Work Start Time</label>
                            <input type="time" class="form-control @error('work_start_time') is-invalid @enderror" id="work_start_time" name="work_start_time" value="{{ old('work_start_time') }}">
                            @error('work_start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="work_end_time" class="form-label">Work End Time</label>
                            <input type="time" class="form-control @error('work_end_time') is-invalid @enderror" id="work_end_time" name="work_end_time" value="{{ old('work_end_time') }}">
                            @error('work_end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Working Days</label>
                            <div class="btn-group" role="group">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <input type="checkbox" class="btn-check" id="day_{{ $day }}" name="working_days[]" value="{{ $day }}" 
                                        {{ in_array($day, old('working_days', [])) ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="day_{{ $day }}">{{ substr($day, 0, 3) }}</label>
                                @endforeach
                            </div>
                            @error('working_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-12 mt-4">
                             <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Doctor is Active</label>
                            </div>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('doctors.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
