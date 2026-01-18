@extends('layouts.dashboard')

@section('title', 'Edit Patient')
@section('page-title', 'Edit Patient')
@section('page-i18n', 'editPatient')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('patients.update', $patient) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Personal Information -->
            <div class="card mb-4 fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                    <span class="badge bg-primary">{{ $patient->patient_code }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name (Primary) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $patient->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">English Name</label>
                            <input type="text" class="form-control" name="name_en" value="{{ old('name_en', $patient->name_en) }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="age" value="{{ old('age', $patient->age) }}" required min="0" max="150">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Nationality</label>
                            <input type="text" class="form-control" name="nationality" value="{{ old('nationality', $patient->nationality) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ID / Passport Number</label>
                            <input type="text" class="form-control" name="id_number" value="{{ old('id_number', $patient->id_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marital Status</label>
                            <select class="form-select" name="marital_status">
                                <option value="">Select Status...</option>
                                <option value="single" {{ old('marital_status', $patient->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ old('marital_status', $patient->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                <option value="divorced" {{ old('marital_status', $patient->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="widowed" {{ old('marital_status', $patient->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" name="occupation" value="{{ old('occupation', $patient->occupation) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Blood Type</label>
                            <select class="form-select" name="blood_type">
                                <option value="">Unknown</option>
                                <option value="A+" {{ old('blood_type', $patient->blood_type) == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_type', $patient->blood_type) == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_type', $patient->blood_type) == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_type', $patient->blood_type) == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_type', $patient->blood_type) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_type', $patient->blood_type) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_type', $patient->blood_type) == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_type', $patient->blood_type) == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" {{ old('status', $patient->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $patient->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="deceased" {{ old('status', $patient->status) == 'deceased' ? 'selected' : '' }}>Deceased</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>Contact & Address</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Primary Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" value="{{ old('phone', $patient->phone) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secondary Phone</label>
                            <input type="tel" class="form-control" name="phone_secondary" value="{{ old('phone_secondary', $patient->phone_secondary) }}">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $patient->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="{{ old('city', $patient->city) }}">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Full Address</label>
                            <textarea class="form-control" name="address" rows="2">{{ old('address', $patient->address) }}</textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" name="emergency_contact" value="{{ old('emergency_contact', $patient->emergency_contact) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Phone</label>
                            <input type="tel" class="form-control" name="emergency_phone" value="{{ old('emergency_phone', $patient->emergency_phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Relationship</label>
                            <input type="text" class="form-control" name="emergency_relation" value="{{ old('emergency_relation', $patient->emergency_relation) }}">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Insurance Information -->
            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Insurance Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Insurance Provider</label>
                            <input type="text" class="form-control" name="insurance_provider" value="{{ old('insurance_provider', $patient->insurance_provider) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Insurance Number / Policy ID</label>
                            <input type="text" class="form-control" name="insurance_number" value="{{ old('insurance_number', $patient->insurance_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" name="insurance_expiry" value="{{ old('insurance_expiry', $patient->insurance_expiry?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical History -->
            <div class="card mb-4 fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Medical History</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Known Allergies</label>
                            <textarea class="form-control" name="allergies" rows="2">{{ old('allergies', $patient->allergies) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Chronic Diseases</label>
                            <textarea class="form-control" name="chronic_diseases" rows="2">{{ old('chronic_diseases', $patient->chronic_diseases) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Current Medications</label>
                            <textarea class="form-control" name="current_medications" rows="2">{{ old('current_medications', $patient->current_medications) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Previous Surgeries</label>
                            <textarea class="form-control" name="previous_surgeries" rows="2">{{ old('previous_surgeries', $patient->previous_surgeries) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Family Medical History</label>
                            <textarea class="form-control" name="family_history" rows="2">{{ old('family_history', $patient->family_history) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-3 mb-5">
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Update Patient</button>
            </div>
        </form>
    </div>
</div>
@endsection
