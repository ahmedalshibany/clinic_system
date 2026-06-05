@extends('layouts.dashboard')

@section('title', $patient->name . ' - ' . __('messages.patientProfile'))
@section('page-title', __('messages.patientProfile'))
@section('page-i18n', 'patientProfile')

@section('styles')
<style>
    .patient-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: var(--border-radius-lg);
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
    }
    .patient-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.3);
        object-fit: cover;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
    }
    .patient-info h2 {
        margin-bottom: 0.25rem;
        font-weight: 700;
    }
    .patient-code {
        font-family: monospace;
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    .patient-badges .badge {
        margin-right: 0.5rem;
        font-weight: 500;
    }
    .nav-tabs-custom {
        border: none;
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        padding: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: var(--text-muted);
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
    }
    .nav-tabs-custom .nav-link.active {
        background: var(--primary);
        color: white;
    }
    .nav-tabs-custom .nav-link:hover:not(.active) {
        background: rgba(var(--primary-rgb), 0.1);
    }
    .info-card {
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        height: 100%;
    }
    .info-card h6 {
        color: var(--text-muted);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    .info-card p {
        margin-bottom: 0;
        font-weight: 500;
    }
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--border-color);
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 0.25rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary);
        border: 3px solid var(--card-bg);
    }
    .file-card {
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.2s;
    }
    .file-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .file-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stat-mini {
        text-align: center;
        padding: 1rem;
    }
    .stat-mini .number {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
    }
    .stat-mini .label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
    }
</style>
@endsection

@section('content')
<!-- Flash Messages -->


<!-- Patient Header -->
<div class="patient-header fade-in">
    <div class="row align-items-center">
        <div class="col-auto">
            @if($patient->photo)
                <img src="{{ asset('storage/' . $patient->photo) }}" alt="{{ $patient->name }}" class="patient-avatar">
            @else
                <div class="patient-avatar">
                    <i class="fas fa-user"></i>
                </div>
            @endif
        </div>
        <div class="col patient-info">
            <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                <h2>{{ $patient->name }}</h2>
                <span class="patient-code">{{ $patient->patient_code ?? 'PAT-' . str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}</span>
                @if($patient->status === 'active')
                    <span class="badge bg-success" data-i18n="active">{{ __('messages.active') }}</span>
                @elseif($patient->status === 'inactive')
                    <span class="badge bg-warning" data-i18n="inactive">{{ __('messages.inactive') }}</span>
                @else
                    <span class="badge bg-secondary" data-i18n="unknown">{{ ucfirst($patient->status ?? __('messages.unknown')) }}</span>
                @endif
            </div>
            <div class="patient-badges">
                <span class="badge   text-dark"><i class="fas fa-birthday-cake me-1"></i>{{ $patient->age ?? $patient->calculated_age ?? '-' }} {{ __('messages.yearsShort') }}</span>
                <span class="badge   text-dark"><i class="fas fa-venus-mars me-1"></i>{{ ucfirst($patient->gender) }}</span>
                @if($patient->blood_type)
                    <span class="badge bg-danger"><i class="fas fa-tint me-1"></i>{{ $patient->blood_type }}</span>
                @endif
                @if($patient->phone)
                    <span class="badge   text-dark"><i class="fas fa-phone me-1"></i>{{ $patient->phone }}</span>
                @endif
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-light">
                    <i class="fas fa-edit me-1"></i> {{ __('messages.edit') }}
                </a>
                <a href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}" class="btn btn-light">
                    <i class="fas fa-calendar-plus me-1"></i> {{ __('messages.bookAppt') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4 fade-in">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body stat-mini">
                <div class="number">{{ $appointmentStats['total'] }}</div>
                <div class="label" data-i18n="totalVisits">{{ __('messages.totalVisits') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body stat-mini">
                <div class="number text-success">{{ $appointmentStats['completed'] }}</div>
                <div class="label" data-i18n="completed">{{ __('messages.completed') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body stat-mini">
                <div class="number text-info">{{ $appointmentStats['upcoming'] }}</div>
                <div class="label" data-i18n="upcoming">{{ __('messages.upcoming') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body stat-mini">
                <div class="number text-primary">{{ $patient->files->count() }}</div>
                <div class="label" data-i18n="documentsLabel">{{ __('messages.documentsLabel') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<ul class="nav nav-tabs-custom fade-in" id="patientTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                            <i class="fas fa-user me-1"></i> <span data-i18n="overview">{{ __('messages.overview') }}</span>
                        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#appointments" type="button">
                            <i class="fas fa-calendar me-1"></i> <span data-i18n="appointments">{{ __('messages.appointments') }}</span>
                        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#medical" type="button">
                            <i class="fas fa-notes-medical me-1"></i> <span data-i18n="medicalRecords">{{ __('messages.medicalRecords') }}</span>
                        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents" type="button">
                            <i class="fas fa-folder me-1"></i> <span data-i18n="documentsLabel">{{ __('messages.documentsLabel') }}</span>
                        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#invoices" type="button">
                            <i class="fas fa-file-invoice-dollar me-1"></i> <span data-i18n="invoices">{{ __('messages.invoicesShort') }}</span>
                        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content fade-in" id="patientTabsContent">
    <!-- Overview Tab -->
    <div class="tab-pane fade show active" id="overview">
        <div class="row g-4">
            <!-- Contact Information -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>{{ __('messages.contactInformation') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="phonePrimary">{{ __('messages.phonePrimary') }}</h6>
                                    <p><span dir="ltr">{{ $patient->phone ?? '-' }}</span></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="phoneSecondary">{{ __('messages.phoneSecondary') }}</h6>
                                    <p><span dir="ltr">{{ $patient->phone_secondary ?? '-' }}</span></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="email">{{ __('messages.email') }}</h6>
                                    <p>{{ $patient->email ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="city">{{ __('messages.city') }}</h6>
                                    <p>{{ $patient->city ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-card">
                                    <h6 data-i18n="address">{{ __('messages.address') }}</h6>
                                    <p>{{ $patient->address ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>{{ __('messages.personalInfo') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="dateOfBirth">{{ __('messages.dateOfBirth') }}</h6>
                                    <p>{{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : '-' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="nationality">{{ __('messages.nationality') }}</h6>
                                    <p>{{ $patient->nationality ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="idNumber">{{ __('messages.idNumber') }}</h6>
                                    <p>{{ $patient->id_number ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="maritalStatus">{{ __('messages.maritalStatus') }}</h6>
                                    <p>{{ ucfirst($patient->marital_status ?? '-') }}</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-card">
                                    <h6 data-i18n="occupation">{{ __('messages.occupation') }}</h6>
                                    <p>{{ $patient->occupation ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Summary -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>{{ __('messages.medicalSummary') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="bloodType">{{ __('messages.bloodType') }}</h6>
                                    <p class="text-danger fw-bold">{{ $patient->blood_type ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="knownAllergies">{{ __('messages.knownAllergies') }}</h6>
                                    <p>{{ $patient->allergies ?? __('messages.noneReported') }}</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-card">
                                    <h6 data-i18n="chronicDiseases">{{ __('messages.chronicDiseases') }}</h6>
                                    <p>{{ $patient->chronic_diseases ?? __('messages.noneReported') }}</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-card">
                                    <h6 data-i18n="currentMedications">{{ __('messages.currentMedications') }}</h6>
                                    <p>{{ $patient->current_medications ?? __('messages.noneReported') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency & Insurance -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>{{ __('messages.emergencyInsurance') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="emergencyContact">{{ __('messages.emergencyContact') }}</h6>
                                    <p>{{ $patient->emergency_contact ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="emergencyPhone">{{ __('messages.emergencyPhone') }}</h6>
                                    <p><span dir="ltr">{{ $patient->emergency_phone ?? '-' }}</span></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="insuranceProvider">{{ __('messages.insuranceProvider') }}</h6>
                                    <p>{{ $patient->insurance_provider ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-card">
                                    <h6 data-i18n="insuranceNumberShort">{{ __('messages.insuranceNumberShort') }}</h6>
                                    <p>{{ $patient->insurance_number ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Tab -->
    <div class="tab-pane fade" id="appointments">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>{{ __('messages.appointmentHistory') }}</h5>
                <a href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('messages.bookNew') }}
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="">
                            <tr>
                                <th data-i18n="date">{{ __('messages.date') }}</th>
                                <th data-i18n="time">{{ __('messages.time') }}</th>
                                <th data-i18n="doctor">{{ __('messages.doctor') }}</th>
                                <th data-i18n="type">{{ __('messages.type') }}</th>
                                <th data-i18n="status">{{ __('messages.status') }}</th>
                                <th data-i18n="actions">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patient->appointments->sortByDesc('date') as $appointment)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->time)->format('h:i A') }}</td>
                                    <td>{{ $appointment->doctor->name ?? '-' }}</td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($appointment->type) }}</span></td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-soft-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                        <p class="mb-0" data-i18n="noAppointmentsFound">{{ __('messages.noAppointmentsFound') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Medical Records Tab -->
    <div class="tab-pane fade" id="medical">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>{{ __('messages.medicalHistory') }}</h5>
            </div>
            <div class="card-body">
                @if($patient->medical_history || $patient->previous_surgeries || $patient->family_history)
                    <div class="row g-4">
                        @if($patient->medical_history)
                            <div class="col-12">
                                <h6 class="text-muted mb-2" data-i18n="generalMedicalHistory">{{ __('messages.generalMedicalHistory') }}</h6>
                                <p>{{ $patient->medical_history }}</p>
                            </div>
                        @endif
                        @if($patient->previous_surgeries)
                            <div class="col-12">
                                <h6 class="text-muted mb-2" data-i18n="previousSurgeries">{{ __('messages.previousSurgeries') }}</h6>
                                <p>{{ $patient->previous_surgeries }}</p>
                            </div>
                        @endif
                        @if($patient->family_history)
                            <div class="col-12">
                                <h6 class="text-muted mb-2" data-i18n="familyHistory">{{ __('messages.familyHistory') }}</h6>
                                <p>{{ $patient->family_history }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-file-medical fa-3x mb-3"></i>
                        <p data-i18n="noMedicalRecordsYet">{{ __('messages.noMedicalRecordsYet') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Documents Tab -->
    <div class="tab-pane fade" id="documents">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-folder me-2"></i>{{ __('messages.documentsLabel') }}</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-upload me-1"></i> {{ __('messages.uploadFile') }}
                </button>
            </div>
            <div class="card-body">
                @if($patient->files->count() > 0)
                    <div class="row g-3">
                        @foreach($patient->files as $file)
                            <div class="col-md-4">
                                <div class="file-card">
                                    <div class="file-icon  ">
                                        <i class="{{ $file->icon_class }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 text-truncate" title="{{ $file->original_name }}">{{ $file->original_name }}</h6>
                                        <small class="text-muted">{{ $file->formatted_size }} • {{ $file->category_label }}</small>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('patients.download-file', [$patient, $file]) }}" class="btn btn-soft-primary btn-sm" title="{{ __('messages.download') }}">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form action="{{ route('patients.delete-file', [$patient, $file]) }}" method="POST" class="d-inline" onsubmit="return confirm(window.translations[document.documentElement.lang || 'en'].confirmDeleteFile)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm" title="{{ __('messages.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p data-i18n="noDocumentsUploaded">{{ __('messages.noDocumentsUploaded') }}</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload me-1"></i> {{ __('messages.uploadFirstDocument') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Invoices Tab -->
    <div class="tab-pane fade" id="invoices">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>{{ __('messages.billingHistory') }}</h5>
            </div>
            <div class="card-body">
                @if($patient->invoices && $patient->invoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="">
                                <tr>
                                    <th data-i18n="invoiceNumber">{{ __('messages.invoiceNumber') }}</th>
                                    <th data-i18n="date">{{ __('messages.date') }}</th>
                                    <th data-i18n="status">{{ __('messages.status') }}</th>
                                    <th data-i18n="total">{{ __('messages.total') }}</th>
                                    <th data-i18n="paid">{{ __('messages.paid') }}</th>
                                    <th data-i18n="balance">{{ __('messages.balance') }}</th>
                                    <th data-i18n="actions">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patient->invoices->sortByDesc('created_at') as $invoice)
                                    <tr>
                                        <td class="fw-bold">{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $statusClass = match($invoice->status) {
                                                    'paid' => 'success',
                                                    'partial' => 'info',
                                                    'draft' => 'secondary',
                                                    'cancelled' => 'danger',
                                                    'overdue' => 'warning',
                                                    default => 'primary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($invoice->status) }}</span>
                                        </td>
                                        <td>${{ number_format($invoice->total, 2) }}</td>
                                        <td class="text-success">${{ number_format($invoice->amount_paid, 2) }}</td>
                                        <td class="text-danger fw-bold">${{ number_format($invoice->total - $invoice->amount_paid, 2) }}</td>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> {{ __('messages.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-file-invoice fa-3x mb-3"></i>
                        <p data-i18n="noInvoicesFound">{{ __('messages.noInvoicesFound') }}</p>
                        <a href="{{ route('invoices.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus me-1"></i> {{ __('messages.createInvoice') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-glass border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" data-i18n="uploadDocument">{{ __('messages.uploadDocument') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('patients.upload-file', $patient) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.fileLabel') }} <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file" required accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted" data-i18n="allowedFileTypes">{{ __('messages.allowedFileTypes') }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" required>
                            <option value="lab_result">{{ __('messages.labResult') }}</option>
                            <option value="xray">{{ __('messages.xray') }}</option>
                            <option value="mri">{{ __('messages.mri') }}</option>
                            <option value="prescription">{{ __('messages.prescription') }}</option>
                            <option value="report">{{ __('messages.report') }}</option>
                            <option value="other">{{ __('messages.other') }}</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.description') }}</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="{{ __('messages.optionalDescription') }}"></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> {{ __('messages.upload') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="mt-4">
    <a href="{{ url()->previous() && url()->previous() !== url()->current() ? url()->previous() : route('patients.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('messages.backToPatients') }}
    </a>
@endsection
