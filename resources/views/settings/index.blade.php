@extends('layouts.dashboard')

@section('title', 'System Settings')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-0 fw-bold">Settings & Configuration</h4>
        <p class="text-muted small">Manage clinic details, system preferences, and other configurations.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <!-- Settings Navigation -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="list-group list-group-flush" id="settingsTab" role="tablist">
                <a class="list-group-item list-group-item-action active py-3 border-0 d-flex align-items-center" 
                   id="clinic-tab" data-bs-toggle="list" href="#clinic" role="tab" aria-controls="clinic">
                    <i class="fas fa-hospital me-3 text-primary"></i>
                    <div>
                        <div class="fw-bold">Clinic Info</div>
                        <small class="text-muted">Logo, Address, Contacts</small>
                    </div>
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center" 
                   id="system-tab" data-bs-toggle="list" href="#system" role="tab" aria-controls="system">
                    <i class="fas fa-sliders-h me-3 text-info"></i>
                    <div>
                        <div class="fw-bold">System</div>
                        <small class="text-muted">Language, Time, Currency</small>
                    </div>
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center" 
                   id="appointments-tab" data-bs-toggle="list" href="#appointments" role="tab" aria-controls="appointments">
                    <i class="fas fa-calendar-check me-3 text-success"></i>
                    <div>
                        <div class="fw-bold">Appointments</div>
                        <small class="text-muted">Slots, Buffers, Rules</small>
                    </div>
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center" 
                   id="invoices-tab" data-bs-toggle="list" href="#invoices" role="tab" aria-controls="invoices">
                    <i class="fas fa-file-invoice-dollar me-3 text-warning"></i>
                    <div>
                        <div class="fw-bold">Invoices</div>
                        <small class="text-muted">Tax, Prefixes, Terms</small>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="tab-content" id="settingsTabContent">
                
                <!-- Clinic Info Tab -->
                <div class="tab-pane fade show active" id="clinic" role="tabpanel" aria-labelledby="clinic-tab">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-primary">Clinic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Logo -->
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Clinic Logo</label>
                                    <div class="d-flex align-items-center">
                                        @if(isset($settings['clinic_logo']))
                                            <img src="{{ asset('storage/'.$settings['clinic_logo']) }}" alt="Logo" class="img-thumbnail me-3" style="height: 80px;">
                                        @endif
                                        <input type="file" name="logo" class="form-control w-auto">
                                    </div>
                                    <div class="form-text">Recommended size: 300x100px. Max size: 2MB.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Clinic Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="clinic_name" class="form-control" value="{{ $settings['clinic_name'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Clinic Name (Arabic)</label>
                                    <input type="text" name="clinic_name_ar" class="form-control" value="{{ $settings['clinic_name_ar'] ?? '' }}" dir="rtl">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Address</label>
                                    <input type="text" name="clinic_address" class="form-control" value="{{ $settings['clinic_address'] ?? '' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="text" name="clinic_phone" class="form-control" value="{{ $settings['clinic_phone'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email Address</label>
                                    <input type="email" name="clinic_email" class="form-control" value="{{ $settings['clinic_email'] ?? '' }}">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Website</label>
                                    <input type="url" name="clinic_website" class="form-control" value="{{ $settings['clinic_website'] ?? '' }}" placeholder="https://">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tax Registration Number</label>
                                    <input type="text" name="tax_number" class="form-control" value="{{ $settings['tax_number'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Tab -->
                <div class="tab-pane fade" id="system" role="tabpanel" aria-labelledby="system-tab">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-info">System Preferences</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Default Language</label>
                                    <select name="default_language" class="form-select">
                                        <option value="en" {{ ($settings['default_language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ar" {{ ($settings['default_language'] ?? 'en') == 'ar' ? 'selected' : '' }}>Arabic</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Timezone</label>
                                    <select name="timezone" class="form-select">
                                        <option value="UTC" {{ ($settings['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                        <option value="Asia/Riyadh" {{ ($settings['timezone'] ?? '') == 'Asia/Riyadh' ? 'selected' : '' }}>Riyadh (GMT+3)</option>
                                        <option value="Asia/Dubai" {{ ($settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>Dubai (GMT+4)</option>
                                        <option value="Africa/Cairo" {{ ($settings['timezone'] ?? '') == 'Africa/Cairo' ? 'selected' : '' }}>Cairo (GMT+2)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Date Format</label>
                                    <select name="date_format" class="form-select">
                                        <option value="Y-m-d" {{ ($settings['date_format'] ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2025-01-20)</option>
                                        <option value="d/m/Y" {{ ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (20/01/2025)</option>
                                        <option value="m/d/Y" {{ ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (01/20/2025)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Time Format</label>
                                    <select name="time_format" class="form-select">
                                        <option value="H:i" {{ ($settings['time_format'] ?? 'H:i') == 'H:i' ? 'selected' : '' }}>24 Hour (14:30)</option>
                                        <option value="h:i A" {{ ($settings['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }}>12 Hour (02:30 PM)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Currency Code</label>
                                    <input type="text" name="currency" class="form-control" value="{{ $settings['currency'] ?? 'USD' }}" maxlength="3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Currency Symbol</label>
                                    <input type="text" name="currency_symbol" class="form-control" value="{{ $settings['currency_symbol'] ?? '$' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments Tab -->
                <div class="tab-pane fade" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-success">Appointment Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Default Slot Duration (Minutes)</label>
                                    <select name="appointment_slot_duration" class="form-select">
                                        <option value="15" {{ ($settings['appointment_slot_duration'] ?? 30) == 15 ? 'selected' : '' }}>15 Minutes</option>
                                        <option value="20" {{ ($settings['appointment_slot_duration'] ?? 30) == 20 ? 'selected' : '' }}>20 Minutes</option>
                                        <option value="30" {{ ($settings['appointment_slot_duration'] ?? 30) == 30 ? 'selected' : '' }}>30 Minutes</option>
                                        <option value="45" {{ ($settings['appointment_slot_duration'] ?? 30) == 45 ? 'selected' : '' }}>45 Minutes</option>
                                        <option value="60" {{ ($settings['appointment_slot_duration'] ?? 30) == 60 ? 'selected' : '' }}>60 Minutes</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Buffer Between Appointments</label>
                                    <select name="buffer_time" class="form-select">
                                        <option value="0" {{ ($settings['buffer_time'] ?? 0) == 0 ? 'selected' : '' }}>None</option>
                                        <option value="5" {{ ($settings['buffer_time'] ?? 0) == 5 ? 'selected' : '' }}>5 Minutes</option>
                                        <option value="10" {{ ($settings['buffer_time'] ?? 0) == 10 ? 'selected' : '' }}>10 Minutes</option>
                                        <option value="15" {{ ($settings['buffer_time'] ?? 0) == 15 ? 'selected' : '' }}>15 Minutes</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Advance Booking Days</label>
                                    <input type="number" name="advance_booking_days" class="form-control" value="{{ $settings['advance_booking_days'] ?? 30 }}" min="1" max="365">
                                    <div class="form-text">How many days in advance can a patient book?</div>
                                </div>
                                
                                <div class="col-md-6 d-flex align-items-center">
                                    <div class="form-check form-switch mt-3">
                                        <input type="hidden" name="allow_same_day" value="0">
                                        <input class="form-check-input" type="checkbox" name="allow_same_day" value="1" id="allowSameDay" 
                                            {{ ($settings['allow_same_day'] ?? 1) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="allowSameDay">Allow Same-Day Booking</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices Tab -->
                <div class="tab-pane fade" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-warning">Invoice Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Invoice Number Prefix</label>
                                    <input type="text" name="invoice_prefix" class="form-control" value="{{ $settings['invoice_prefix'] ?? 'INV-' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Default Tax Rate (%)</label>
                                    <input type="number" name="tax_rate" class="form-control" value="{{ $settings['tax_rate'] ?? 0 }}" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Default Due Days</label>
                                    <input type="number" name="default_due_days" class="form-control" value="{{ $settings['default_due_days'] ?? 30 }}">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold">Default Payment Terms</label>
                                    <textarea name="payment_terms" class="form-control" rows="3">{{ $settings['payment_terms'] ?? 'Payment is due within 30 days.' }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Bank Details / Payment Instructions</label>
                                    <textarea name="bank_details" class="form-control" rows="3">{{ $settings['bank_details'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary px-5 btn-lg">
                    <i class="fas fa-save me-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
