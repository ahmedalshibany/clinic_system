@extends('layouts.dashboard')

@section('title', __('messages.patientsCreate'))
@section('page-title', __('messages.patientsCreate'))
@section('page-i18n', 'addPatient')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('patients.store') }}" method="POST">
            @csrf
            
            <!-- Personal Information -->
            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>{{ __('messages.personalInformation') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.fullNamePrimary') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required placeholder="{{ __('messages.placeholderJohnDoe') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.englishName') }}</label>
                            <input type="text" class="form-control" name="name_en" value="{{ old('name_en') }}" placeholder="{{ __('messages.placeholderJohnDoe') }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.dateOfBirth') }}</label>
                            <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.age') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="age" value="{{ old('age') }}" required min="0" max="150" placeholder="{{ __('messages.placeholderAutoCalcAge') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.gender') }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.nationality') }}</label>
                            <input type="text" class="form-control" name="nationality" value="{{ old('nationality') }}" placeholder="{{ __('messages.placeholderNationality') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.idPassportNumber') }}</label>
                            <input type="text" class="form-control" name="id_number" value="{{ old('id_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.maritalStatus') }}</label>
                            <select class="form-select" name="marital_status">
                                <option value="">{{ __('messages.selectStatus') }}</option>
                                <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>{{ __('messages.single') }}</option>
                                <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>{{ __('messages.married') }}</option>
                                <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>{{ __('messages.divorced') }}</option>
                                <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>{{ __('messages.widowed') }}</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.occupation') }}</label>
                            <input type="text" class="form-control" name="occupation" value="{{ old('occupation') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.bloodType') }}</label>
                            <select class="form-select" name="blood_type">
                                <option value="">{{ __('messages.unknown') }}</option>
                                <option value="A+" {{ old('blood_type') == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_type') == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_type') == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_type') == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_type') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_type') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_type') == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_type') == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>{{ __('messages.contactAddress') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.primaryPhone') }} <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.secondaryPhone') }}</label>
                            <input type="tel" class="form-control" name="phone_secondary" value="{{ old('phone_secondary') }}">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.emailAddress') }}</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.city') }}</label>
                            <input type="text" class="form-control" name="city" value="{{ old('city') }}">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.fullAddress') }}</label>
                            <textarea class="form-control" name="address" rows="2">{{ old('address') }}</textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.emergencyContactName') }}</label>
                            <input type="text" class="form-control" name="emergency_contact" value="{{ old('emergency_contact') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.emergencyPhone') }}</label>
                            <input type="tel" class="form-control" name="emergency_phone" value="{{ old('emergency_phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.relationship') }}</label>
                            <input type="text" class="form-control" name="emergency_relation" value="{{ old('emergency_relation') }}" placeholder="{{ __('messages.placeholderSpouseParent') }}">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Insurance Information -->
            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>{{ __('messages.insuranceDetails') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.insuranceProvider') }}</label>
                            <input type="text" class="form-control" name="insurance_provider" value="{{ old('insurance_provider') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.insuranceNumber') }}</label>
                            <input type="text" class="form-control" name="insurance_number" value="{{ old('insurance_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.expiryDate') }}</label>
                            <input type="date" class="form-control" name="insurance_expiry" value="{{ old('insurance_expiry') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical History -->
            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>{{ __('messages.medicalHistory') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.knownAllergies') }}</label>
                            <textarea class="form-control" name="allergies" rows="2" placeholder="{{ __('messages.placeholderAllergies') }}">{{ old('allergies') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.chronicDiseases') }}</label>
                            <textarea class="form-control" name="chronic_diseases" rows="2" placeholder="{{ __('messages.placeholderChronicDiseases') }}">{{ old('chronic_diseases') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.currentMedications') }}</label>
                            <textarea class="form-control" name="current_medications" rows="2">{{ old('current_medications') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.previousSurgeries') }}</label>
                            <textarea class="form-control" name="previous_surgeries" rows="2">{{ old('previous_surgeries') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.familyMedicalHistory') }}</label>
                            <textarea class="form-control" name="family_history" rows="2">{{ old('family_history') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-3 mb-5">
                <a href="{{ url()->previous() && url()->previous() !== url()->current() ? url()->previous() : route('patients.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>{{ __('messages.savePatient') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dobInput = document.querySelector('input[name="date_of_birth"]');
    const ageInput = document.querySelector('input[name="age"]');
    
    if (dobInput && ageInput) {
        dobInput.addEventListener('change', function() {
            if (this.value) {
                const dob = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const monthDiff = today.getMonth() - dob.getMonth();
                
                // Adjust if birthday hasn't occurred yet this year
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                
                if (age >= 0 && age <= 150) {
                    ageInput.value = age;
                }
            }
        });
    }
});
</script>
@endsection
