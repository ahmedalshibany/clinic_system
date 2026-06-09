@extends('layouts.dashboard')

@section('title', __('messages.doctorsEdit'))
@section('page-title', __('messages.doctorsEdit'))
@section('page-i18n', 'doctors')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('doctors.update', $doctor) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>{{ __('messages.personalInformation') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.fullNamePrimary') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $doctor->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.emailAddress') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $doctor->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.phoneNumber') }} <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $doctor->phone) }}" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.specialty') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('specialty') is-invalid @enderror" name="specialty" required>
                                <option value="" disabled>{{ __('messages.selectSpecialty') }}</option>
                                @foreach(['General Practitioner', 'Pediatrics', 'Cardiology', 'Dermatology', 'Orthopedics', 'Dentistry', 'Internal Medicine'] as $spec)
                                    <option value="{{ $spec }}" {{ old('specialty', $doctor->specialty) == $spec ? 'selected' : '' }}>{{ $spec }}</option>
                                @endforeach
                            </select>
                            @error('specialty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.department') }}</label>
                            <input type="text" class="form-control @error('department') is-invalid @enderror" name="department" value="{{ old('department', $doctor->department) }}" placeholder="{{ __('messages.departmentPlaceholder') }}">
                            @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('messages.biography') }}</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" name="bio" rows="3">{{ old('bio', $doctor->bio) }}</textarea>
                            @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>{{ __('messages.workInformation') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.consultationFee') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" class="form-control @error('consultation_fee') is-invalid @enderror" name="consultation_fee" value="{{ old('consultation_fee', $doctor->consultation_fee) }}" min="0" step="0.01">
                            </div>
                            @error('consultation_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.workStartTime') }}</label>
                            <input type="time" class="form-control @error('work_start_time') is-invalid @enderror" name="work_start_time" value="{{ old('work_start_time', $doctor->work_start_time ? date('H:i', strtotime($doctor->work_start_time)) : '') }}">
                            @error('work_start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.workEndTime') }}</label>
                            <input type="time" class="form-control @error('work_end_time') is-invalid @enderror" name="work_end_time" value="{{ old('work_end_time', $doctor->work_end_time ? date('H:i', strtotime($doctor->work_end_time)) : '') }}">
                            @error('work_end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.slotDuration') }}</label>
                            <input type="number" class="form-control @error('slot_duration') is-invalid @enderror" name="slot_duration" value="{{ old('slot_duration', 30) }}" min="5" max="120" step="5">
                            @error('slot_duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">{{ __('messages.workingDays') }}</label>
                            <div class="btn-group flex-wrap" role="group">
                                @php
                                    $dayKeys = ['day_sunday', 'day_monday', 'day_tuesday', 'day_wednesday', 'day_thursday', 'day_friday', 'day_saturday'];
                                    $dayNamesForLookup = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                    $savedDays = old('working_days', $doctor->working_days ?? []);
                                    $savedIndices = collect($savedDays)->map(function($day) use ($dayNamesForLookup) {
                                        return is_numeric($day) ? (int)$day : array_search($day, $dayNamesForLookup);
                                    })->filter(function($v) { return $v !== false; })->values()->toArray();
                                @endphp
                                @foreach($dayKeys as $index => $key)
                                    <input type="checkbox" class="btn-check" id="day_{{ $index }}" name="working_days[]" value="{{ $index }}"
                                        {{ in_array($index, $savedIndices) ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="day_{{ $index }}">{{ __('messages.' . $key) }}</label>
                                @endforeach
                            </div>
                            @error('working_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $doctor->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">{{ __('messages.doctorIsActive') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-camera me-2"></i>{{ __('messages.avatarManagement') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.avatarUrl') }}</label>
                            <input type="url" class="form-control @error('avatar') is-invalid @enderror" name="avatar" value="{{ old('avatar', $doctor->avatar) }}" placeholder="{{ __('messages.placeholderAvatarUrl') }}">
                            @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">{{ __('messages.avatarHint') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mb-5">
                <a href="{{ route('doctors.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>{{ __('messages.updateDoctor') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
