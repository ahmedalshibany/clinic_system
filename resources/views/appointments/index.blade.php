@extends('layouts.dashboard')

@section('title', 'Appointments')
@section('page-title', 'Appointments')
@section('page-i18n', 'appointments')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    /* Fix Select2 inside Modal */
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #dee2e6;
    }
    .select2-dropdown {
        z-index: 9999;
    }
</style>
@endsection

@section('content')

<!-- Flash Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        @foreach($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Action Toolbar -->
<div class="action-toolbar d-flex gap-3 flex-wrap align-items-center justify-content-between mb-4 fade-in">
    <form action="{{ route('appointments.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
        </div>

        <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>

        <input type="date" name="date" class="form-control" style="width: auto;" value="{{ request('date') }}" onchange="this.form.submit()">
    </form>

    <button class="btn btn-primary d-flex align-items-center gap-2 ms-auto" data-bs-toggle="modal" data-bs-target="#appointmentModal">
        <i class="fas fa-plus"></i>
        <span data-i18n="bookAppt">Book Appointment</span>
    </button>
</div>

<!-- Appointments Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">#</th>
                        <th class="py-3" data-i18n="patient">Patient</th>
                        <th class="py-3" data-i18n="doctor">Doctor</th>
                        <th class="py-3" data-i18n="date">Date</th>
                        <th class="py-3" data-i18n="time">Time</th>
                        <th class="py-3" data-i18n="vitals">Vitals</th>
                        <th class="py-3" data-i18n="status">Status</th>
                        <th class="pe-4 py-3 text-center" data-i18n="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'checked_in' => 'primary',
                                'completed' => 'info',
                                'cancelled' => 'danger'
                            ];
                            $badgeColor = $statusColors[$appointment->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">{{ $appointment->id }}</td>
                            <td>
                                <span class="fw-medium">{{ $appointment->patient->name ?? 'Unknown' }}</span>
                            </td>
                            <td>{{ $appointment->doctor->name ?? 'Unknown' }}</td>
                            <td>{{ \Carbon\Carbon::parse($appointment->date)->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($appointment->time)->format('H:i') }}</td>
                            <td>
                                @if($appointment->vital)
                                    <div class="d-flex flex-column gap-1" style="font-size: 0.85rem;">
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" title="Blood Pressure">
                                            <i class="fas fa-heartbeat me-1"></i> BP: {{ $appointment->vital->bp_systolic }}/{{ $appointment->vital->bp_diastolic }}
                                        </span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" title="Temperature">
                                            <i class="fas fa-thermometer-half me-1"></i> {{ $appointment->vital->temperature }}°C
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted small py-1 px-2 bg-light rounded">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} px-3 py-2 rounded-pill" data-i18n="{{ strtolower($appointment->status) }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    @if($appointment->status === 'confirmed')
                                        <form action="{{ route('appointments.check-in', $appointment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3" title="Check In" data-i18n-title="checkIn">
                                                <i class="fas fa-check-square me-1"></i> Check In
                                            </button>
                                        </form>
                                    @elseif($appointment->status === 'waiting')
                                        <form action="{{ route('appointments.start', $appointment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" title="Start Visit">
                                                <i class="fas fa-play me-1"></i> Start Visit
                                            </button>
                                        </form>
                                    @elseif($appointment->status === 'in_progress')
                                        <form action="{{ route('appointments.complete', $appointment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-info text-white btn-sm rounded-pill px-3 fw-bold" title="Complete Visit">
                                                <i class="fas fa-check-double me-1"></i> Complete
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <button class="btn btn-soft-primary btn-sm" onclick="editAppointment({{ json_encode($appointment) }})" title="Edit" data-i18n-title="edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this appointment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" title="Delete" data-i18n-title="delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @if($appointment->status === 'completed' && !$appointment->invoice)
                                    <div class="mt-2 text-center">
                                        <a href="{{ route('invoices.create-from-appointment', $appointment->id) }}" class="btn btn-warning btn-sm w-100 text-dark">
                                            <i class="fas fa-file-invoice-dollar me-1"></i> <span data-i18n="createInvoice">Create Invoice</span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0" data-i18n="noAppointments">No appointments found</p>
                                <p class="text-muted small mt-2" data-i18n="clickToBookAppt">Click "Book Appointment" to get started!</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($appointments->hasPages())
        <div class="pagination-controls">
            <div class="pagination-info">
                <span data-i18n="showing">Showing</span> <strong>{{ $appointments->firstItem() }}-{{ $appointments->lastItem() }}</strong> <span data-i18n="of">of</span> <strong>{{ $appointments->total() }}</strong> <span data-i18n="appointmentsLabel">appointments</span>
            </div>
            <div class="d-flex gap-2">
                @if(!$appointments->onFirstPage())
                    <a href="{{ $appointments->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span></a>
                @endif
                @if($appointments->hasMorePages())
                    <a href="{{ $appointments->nextPageUrl() }}" class="btn btn-light btn-sm"><span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i></a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add/Edit Appointment Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-glass border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="appointmentModalTitle" data-i18n="bookAppt">Book Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="appointmentForm" action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="patient">Patient</label>
                            <select class="form-select" name="patient_id" id="patientSelect" required style="width: 100%;">
                                <option value="">Search for Patient...</option>
                                {{-- Options loaded via AJAX --}}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="doctor">Doctor</label>
                            <select class="form-select" name="doctor_id" id="doctorSelect" required>
                                <option value="">Select Doctor</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->name }} - {{ $doctor->specialty }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="date">Date</label>
                            <input type="date" class="form-control" name="date" id="appointmentDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="time">Time</label>
                            <input type="time" class="form-control" name="time" id="appointmentTime" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="type">Type</label>
                            <select class="form-select" name="type" id="appointmentType" required>
                                <option value="Consultation">Consultation</option>
                                <option value="Checkup">Checkup</option>
                                <option value="Follow-up">Follow-up</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="status">Status</label>
                            <select class="form-select" name="status" id="appointmentStatus" required>
                                <option value="pending" data-i18n="pending">Pending</option>
                                <option value="confirmed" data-i18n="confirmed">Confirmed</option>
                                <option value="completed" data-i18n="completed">Completed</option>
                                <option value="cancelled" data-i18n="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="appointmentNotes" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary" data-i18n="save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection



@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function editAppointment(appt) {
    document.getElementById('appointmentModalTitle').setAttribute('data-i18n', 'editAppt');
    document.getElementById('appointmentModalTitle').textContent = 'Edit Appointment'; // Fallback
    if (window.app && window.app.applyLanguage) window.app.applyLanguage(window.app.lang); // Trigger re-translation

    document.getElementById('appointmentForm').action = '/appointments/' + appt.id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('patientSelect').value = appt.patient_id;
    document.getElementById('doctorSelect').value = appt.doctor_id;
    document.getElementById('appointmentDate').value = appt.date.split('T')[0];
    document.getElementById('appointmentTime').value = appt.time ? appt.time.substring(0, 5) : '';
    document.getElementById('appointmentType').value = appt.type;
    document.getElementById('appointmentStatus').value = appt.status;
    document.getElementById('appointmentNotes').value = appt.notes || '';
    
    // Set Select2 value manually for edit
    if ($('#patientSelect').find("option[value='" + appt.patient_id + "']").length) {
        $('#patientSelect').val(appt.patient_id).trigger('change');
    } else { 
        // Create a DOM Option and pre-select it
        var newOption = new Option(appt.patient.name, appt.patient_id, true, true);
        $('#patientSelect').append(newOption).trigger('change');
    }

    new bootstrap.Modal(document.getElementById('appointmentModal')).show();
}

$(document).ready(function() {
    $('#patientSelect').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#appointmentModal'),
        ajax: {
            url: '{{ route("api.patients.search") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        },
        placeholder: 'Search for patient...',
        minimumInputLength: 1
    });
});

// Reset form when modal is closed
document.getElementById('appointmentModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('appointmentModalTitle').setAttribute('data-i18n', 'bookAppt');
    document.getElementById('appointmentModalTitle').textContent = 'Book Appointment'; // Fallback
    if (window.app && window.app.applyLanguage) window.app.applyLanguage(window.app.lang);

    document.getElementById('appointmentForm').action = '{{ route("appointments.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('appointmentForm').reset();
    $('#patientSelect').val(null).trigger('change');
});

</script>
@endsection
