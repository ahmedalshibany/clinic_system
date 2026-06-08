@extends('layouts.dashboard')

@section('title', __('messages.doctorsCreate'))
@section('page-title', __('messages.doctorsCreate'))
@section('page-i18n', 'doctors')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('doctors.store') }}" method="POST">
            @csrf

            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>{{ __('messages.personalInformation') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.fullNamePrimary') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.emailAddress') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.phoneNumber') }} <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.specialty') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('specialty') is-invalid @enderror" name="specialty" required>
                                <option value="" disabled selected>{{ __('messages.selectSpecialty') }}</option>
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

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.department') }}</label>
                            <input type="text" class="form-control @error('department') is-invalid @enderror" name="department" value="{{ old('department') }}" placeholder="{{ __('messages.departmentPlaceholder') }}">
                            @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('messages.biography') }}</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" name="bio" rows="3">{{ old('bio') }}</textarea>
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
                                <span class="input-group-text">{{ __('messages.currencySymbol') }}</span>
                                <input type="number" class="form-control @error('consultation_fee') is-invalid @enderror" name="consultation_fee" value="{{ old('consultation_fee') }}" min="0" step="0.01">
                            </div>
                            @error('consultation_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.workStartTime') }}</label>
                            <input type="time" class="form-control @error('work_start_time') is-invalid @enderror" name="work_start_time" value="{{ old('work_start_time') }}">
                            @error('work_start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.workEndTime') }}</label>
                            <input type="time" class="form-control @error('work_end_time') is-invalid @enderror" name="work_end_time" value="{{ old('work_end_time') }}">
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
                                @php $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']; @endphp
                                @foreach($dayNames as $index => $day)
                                    <input type="checkbox" class="btn-check" id="day_{{ $index }}" name="working_days[]" value="{{ $index }}"
                                        {{ in_array($index, old('working_days', [])) ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="day_{{ $index }}">{{ substr($day, 0, 3) }}</label>
                                @endforeach
                            </div>
                            @error('working_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
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
                            <input type="url" class="form-control @error('avatar') is-invalid @enderror" name="avatar" value="{{ old('avatar') }}" placeholder="{{ __('messages.placeholderAvatarUrl') }}">
                            @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">{{ __('messages.avatarHint') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mb-5">
                <a href="{{ url()->previous() && url()->previous() !== url()->current() ? url()->previous() : route('doctors.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>{{ __('messages.saveDoctor') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
