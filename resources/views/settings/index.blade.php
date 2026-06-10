@extends('layouts.dashboard')

@section('title', __('messages.settingsTitle'))
@section('page-title', __('messages.settingsTitle'))
@section('page-i18n', 'settings')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-0 fw-bold" data-i18n="settingsTitle">{{ __('messages.settingsTitle') }}</h4>
        <p class="text-muted small" data-i18n="settingsSubtitle">{{ __('messages.settingsSubtitle') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <!-- Settings Navigation — Palazzo Nav -->
        <div class="settings-nav" id="settingsTab" role="tablist">
            <a class="settings-nav-item active" 
               id="clinic-tab" data-bs-toggle="list" href="#clinic" role="tab" aria-controls="clinic">
                <i class="fas fa-clinic-medical"></i>
                <div>
                    <div class="settings-nav-title" data-i18n="clinicInfo">{{ __('messages.clinicInfo') }}</div>
                    <div class="settings-nav-subtitle" data-i18n="clinicInfoDesc">{{ __('messages.clinicInfoDesc') ?? 'Logo, Address, Contacts' }}</div>
                </div>
            </a>
            <a class="settings-nav-item" 
               id="system-tab" data-bs-toggle="list" href="#system" role="tab" aria-controls="system">
                <i class="fas fa-sliders-h"></i>
                <div>
                    <div class="settings-nav-title" data-i18n="systemPref">{{ __('messages.systemPref') }}</div>
                    <div class="settings-nav-subtitle" data-i18n="systemPrefDesc">{{ __('messages.systemPrefDesc') ?? 'Language, Time, Currency' }}</div>
                </div>
            </a>
            <a class="settings-nav-item" 
               id="appointments-tab" data-bs-toggle="list" href="#appointments" role="tab" aria-controls="appointments">
                <i class="fas fa-calendar-alt"></i>
                <div>
                    <div class="settings-nav-title" data-i18n="apptSettings">{{ __('messages.apptSettings') }}</div>
                    <div class="settings-nav-subtitle" data-i18n="apptSettingsDesc">{{ __('messages.apptSettingsDesc') ?? 'Slots, Buffers, Rules' }}</div>
                </div>
            </a>
            <a class="settings-nav-item" 
               id="invoices-tab" data-bs-toggle="list" href="#invoices" role="tab" aria-controls="invoices">
                <i class="fas fa-file-invoice-dollar"></i>
                <div>
                    <div class="settings-nav-title" data-i18n="invSettings">{{ __('messages.invSettings') }}</div>
                    <div class="settings-nav-subtitle" data-i18n="invSettingsDesc">{{ __('messages.invSettingsDesc') ?? 'Tax, Prefixes, Terms' }}</div>
                </div>
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="tab-content" id="settingsTabContent">
                
                <!-- Clinic Info Tab -->
                <div class="tab-pane fade show active fade-in" id="clinic" role="tabpanel" aria-labelledby="clinic-tab">
                    <div class="card settings-card fade-in border-0 shadow-sm">
                        <div class="card-header">
                            <i class="fas fa-clinic-medical"></i>
                            <h6 data-i18n="clinicInfoSection">{{ __('messages.clinicInfoSection') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="branding">{{ __('messages.branding') ?? 'Branding' }}</h6>
                                <div class="row g-3">
                                    <!-- Logo -->
                                    <div class="col-12">
                                        <label class="form-label" data-i18n="clinicLogo">{{ __('messages.clinicLogo') }}</label>
                                        <div class="d-flex align-items-center">
                                            @if(isset($settings['clinic_logo']))
                                                <img src="{{ asset('storage/'.$settings['clinic_logo']) }}" alt="Logo" class="img-thumbnail me-3" style="height: 80px;">
                                            @endif
                                            <input type="file" name="logo" class="form-control w-auto">
                                        </div>
                                        <div class="form-text" data-i18n="logoDimensions">{{ __('messages.logoDimensions') }}</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="settings-divider">

                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="contactDetails">{{ __('messages.contactDetails') ?? 'Contact Details' }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="clinicNameEn">{{ __('messages.clinicNameEn') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="clinic_name" class="form-control" value="{{ old('clinic_name', $settings['clinic_name'] ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="clinicNameAr">{{ __('messages.clinicNameAr') }}</label>
                                        <input type="text" name="clinic_name_ar" class="form-control" value="{{ old('clinic_name_ar', $settings['clinic_name_ar'] ?? '') }}" dir="rtl">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" data-i18n="address">{{ __('messages.address') }}</label>
                                        <input type="text" name="clinic_address" class="form-control" value="{{ old('clinic_address', $settings['clinic_address'] ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="phone">{{ __('messages.phone') }}</label>
                                        <input type="text" name="clinic_phone" class="form-control" value="{{ old('clinic_phone', $settings['clinic_phone'] ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="email">{{ __('messages.email') }}</label>
                                        <input type="email" name="clinic_email" class="form-control" value="{{ old('clinic_email', $settings['clinic_email'] ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="settings-divider">

                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="registration">{{ __('messages.registration') ?? 'Registration' }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="website">{{ __('messages.website') }}</label>
                                        <input type="url" name="clinic_website" class="form-control" value="{{ old('clinic_website', $settings['clinic_website'] ?? '') }}" placeholder="https://" dir="ltr">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="taxNumber">{{ __('messages.taxNumber') }}</label>
                                        <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $settings['tax_number'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Tab -->
                <div class="tab-pane fade fade-in" id="system" role="tabpanel" aria-labelledby="system-tab">
                    <div class="card settings-card fade-in border-0 shadow-sm">
                        <div class="card-header">
                            <i class="fas fa-sliders-h"></i>
                            <h6 data-i18n="systemPrefSection">{{ __('messages.systemPrefSection') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="locale">{{ __('messages.locale') ?? 'Locale' }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="defaultLang">{{ __('messages.defaultLang') }}</label>
                                        <select name="default_language" class="form-select">
                                            <option value="en" {{ old('default_language', $settings['default_language'] ?? 'en') == 'en' ? 'selected' : '' }} data-i18n="english">{{ __('messages.english') }}</option>
                                            <option value="ar" {{ old('default_language', $settings['default_language'] ?? 'en') == 'ar' ? 'selected' : '' }} data-i18n="arabic">{{ __('messages.arabic') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="timezone">{{ __('messages.timezone') }}</label>
                                        <select name="timezone" class="form-select">
                                            <option value="UTC" {{ old('timezone', $settings['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }} data-i18n="tzUtc">{{ __('messages.tzUtc') ?? 'UTC' }}</option>
                                            <option value="Asia/Riyadh" {{ old('timezone', $settings['timezone'] ?? '') == 'Asia/Riyadh' ? 'selected' : '' }} data-i18n="tzRiyadh">{{ __('messages.tzRiyadh') ?? 'Riyadh (GMT+3)' }}</option>
                                            <option value="Asia/Dubai" {{ old('timezone', $settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' }} data-i18n="tzDubai">{{ __('messages.tzDubai') ?? 'Dubai (GMT+4)' }}</option>
                                            <option value="Africa/Cairo" {{ old('timezone', $settings['timezone'] ?? '') == 'Africa/Cairo' ? 'selected' : '' }} data-i18n="tzCairo">{{ __('messages.tzCairo') ?? 'Cairo (GMT+2)' }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="settings-divider">

                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="formats">{{ __('messages.formats') ?? 'Formats' }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="dateFormat">{{ __('messages.dateFormat') }}</label>
                                        <select name="date_format" class="form-select">
                                            <option value="Y-m-d" {{ old('date_format', $settings['date_format'] ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }} data-i18n="fmtYmd">{{ __('messages.fmtYmd') ?? 'YYYY-MM-DD (2025-01-20)' }}</option>
                                            <option value="d/m/Y" {{ old('date_format', $settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }} data-i18n="fmtDmy">{{ __('messages.fmtDmy') ?? 'DD/MM/YYYY (20/01/2025)' }}</option>
                                            <option value="m/d/Y" {{ old('date_format', $settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }} data-i18n="fmtMdy">{{ __('messages.fmtMdy') ?? 'MM/DD/YYYY (01/20/2025)' }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="timeFormat">{{ __('messages.timeFormat') }}</label>
                                        <select name="time_format" class="form-select">
                                            <option value="H:i" {{ old('time_format', $settings['time_format'] ?? 'H:i') == 'H:i' ? 'selected' : '' }} data-i18n="fmt24h">{{ __('messages.fmt24h') ?? '24 Hour (14:30)' }}</option>
                                            <option value="h:i A" {{ old('time_format', $settings['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }} data-i18n="fmt12h">{{ __('messages.fmt12h') ?? '12 Hour (02:30 PM)' }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="settings-divider">

                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="currency">{{ __('messages.currency') ?? 'Currency' }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="currencyCode">{{ __('messages.currencyCode') }}</label>
                                        <input type="text" name="currency" class="form-control" value="{{ old('currency', $settings['currency'] ?? 'USD') }}" maxlength="3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="currencySymbolLabel">{{ __('messages.currencySymbolLabel') }}</label>
                                        <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '$') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments Tab -->
                <div class="tab-pane fade fade-in" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
                    <div class="card settings-card fade-in border-0 shadow-sm">
                        <div class="card-header">
                            <i class="fas fa-calendar-alt"></i>
                            <h6 data-i18n="apptSettingsSection">{{ __('messages.apptSettingsSection') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="scheduling">{{ __('messages.scheduling') ?? 'Scheduling' }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="slotDuration">{{ __('messages.slotDuration') }}</label>
                                        <select name="appointment_slot_duration" class="form-select">
                                            <option value="15" {{ old('appointment_slot_duration', $settings['appointment_slot_duration'] ?? 30) == 15 ? 'selected' : '' }} data-i18n="minutes15">{{ __('messages.minutes15') ?? '15 Minutes' }}</option>
                                            <option value="20" {{ old('appointment_slot_duration', $settings['appointment_slot_duration'] ?? 30) == 20 ? 'selected' : '' }} data-i18n="minutes20">{{ __('messages.minutes20') ?? '20 Minutes' }}</option>
                                            <option value="30" {{ old('appointment_slot_duration', $settings['appointment_slot_duration'] ?? 30) == 30 ? 'selected' : '' }} data-i18n="minutes30">{{ __('messages.minutes30') ?? '30 Minutes' }}</option>
                                            <option value="45" {{ old('appointment_slot_duration', $settings['appointment_slot_duration'] ?? 30) == 45 ? 'selected' : '' }} data-i18n="minutes45">{{ __('messages.minutes45') ?? '45 Minutes' }}</option>
                                            <option value="60" {{ old('appointment_slot_duration', $settings['appointment_slot_duration'] ?? 30) == 60 ? 'selected' : '' }} data-i18n="minutes60">{{ __('messages.minutes60') ?? '60 Minutes' }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="bufferTime">{{ __('messages.bufferTime') }}</label>
                                        <select name="buffer_time" class="form-select">
                                            <option value="0" {{ old('buffer_time', $settings['buffer_time'] ?? 0) == 0 ? 'selected' : '' }} data-i18n="none">{{ __('messages.none') }}</option>
                                            <option value="5" {{ old('buffer_time', $settings['buffer_time'] ?? 0) == 5 ? 'selected' : '' }} data-i18n="minutes5">{{ __('messages.minutes5') ?? '5 Minutes' }}</option>
                                            <option value="10" {{ old('buffer_time', $settings['buffer_time'] ?? 0) == 10 ? 'selected' : '' }} data-i18n="minutes10">{{ __('messages.minutes10') ?? '10 Minutes' }}</option>
                                            <option value="15" {{ old('buffer_time', $settings['buffer_time'] ?? 0) == 15 ? 'selected' : '' }} data-i18n="minutes15">{{ __('messages.minutes15') ?? '15 Minutes' }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="advanceBooking">{{ __('messages.advanceBooking') }}</label>
                                        <input type="number" name="advance_booking_days" class="form-control" value="{{ old('advance_booking_days', $settings['advance_booking_days'] ?? 30) }}" min="1" max="365">
                                        <div class="form-text" data-i18n="advanceBookingDesc">{{ __('messages.advanceBookingDesc') }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="toggle-switch">
                                            <input type="hidden" name="allow_same_day" value="0">
                                            <input type="checkbox" name="allow_same_day" value="1" id="allowSameDay" 
                                                {{ old('allow_same_day', $settings['allow_same_day'] ?? 1) ? 'checked' : '' }}>
                                            <span class="toggle-track">
                                                <span class="toggle-knob"></span>
                                            </span>
                                            <span class="toggle-label" data-i18n="allowSameDay">{{ __('messages.allowSameDay') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="settings-divider">

                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="operatingHours">{{ __('messages.operatingHours') ?? 'Operating Hours' }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="opStart">{{ __('messages.opStart') }}</label>
                                        <input type="time" name="start_hour" class="form-control" value="{{ old('start_hour', $settings['start_hour'] ?? '09:00') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="opEnd">{{ __('messages.opEnd') }}</label>
                                        <input type="time" name="end_hour" class="form-control" value="{{ old('end_hour', $settings['end_hour'] ?? '17:00') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices Tab -->
                <div class="tab-pane fade fade-in" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                    <div class="card settings-card fade-in border-0 shadow-sm">
                        <div class="card-header">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <h6 data-i18n="invConfigSection">{{ __('messages.invConfigSection') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="invoiceDefaults">{{ __('messages.invoiceDefaults') ?? 'Invoice Defaults' }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="invPrefix">{{ __('messages.invPrefix') }}</label>
                                        <input type="text" name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV-') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="taxRate">{{ __('messages.taxRate') }}</label>
                                        <input type="number" name="tax_rate" class="form-control" value="{{ old('tax_rate', $settings['tax_rate'] ?? 0) }}" min="0" max="100" step="0.01">
                                        <span class="text-danger" data-i18n="taxRateWarning">{{ __('messages.taxRateWarning') }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" data-i18n="dueDays">{{ __('messages.dueDays') }}</label>
                                        <input type="number" name="default_due_days" class="form-control" value="{{ old('default_due_days', $settings['default_due_days'] ?? 30) }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="settings-divider">

                            <div class="settings-section">
                                <h6 class="settings-section-title" data-i18n="termsAndDetails">{{ __('messages.termsAndDetails') ?? 'Terms & Bank Details' }}</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" data-i18n="paymentTerms">{{ __('messages.paymentTerms') }}</label>
                                        <textarea name="payment_terms" class="form-control" rows="3">{{ old('payment_terms', $settings['payment_terms'] ?? 'Payment is due within 30 days.') }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" data-i18n="bankDetails">{{ __('messages.bankDetails') }}</label>
                                        <textarea name="bank_details" class="form-control" rows="3">{{ old('bank_details', $settings['bank_details'] ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="settings-save-footer">
                <button type="submit" class="btn btn-primary px-5 btn-lg">
                    <i class="fas fa-save me-2"></i> <span data-i18n="saveChanges">{{ __('messages.saveChanges') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
