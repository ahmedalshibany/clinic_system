@extends('layouts.dashboard')

@section('title', __('messages.appointments'))
@section('page-title', __('messages.appointments'))
@section('page-i18n', 'appointments')

@section('styles')
@endsection

@section('content')

<!-- Flash Messages -->


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
            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }} data-i18n="allStatus">All Status</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }} data-i18n="pending">Pending</option>
            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }} data-i18n="confirmed">Confirmed</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }} data-i18n="completed">Completed</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }} data-i18n="cancelled">Cancelled</option>
        </select>

        <input type="date" name="date" class="form-control" style="width: auto;" value="{{ request('date') }}" onchange="this.form.submit()">
    </form>

    <a href="{{ route('appointments.create') }}" class="btn btn-primary d-flex align-items-center gap-2 ms-auto">
        <i class="fas fa-plus"></i>
        <span data-i18n="bookAppt">Book Appointment</span>
    </a>
</div>

<!-- Appointments Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="">
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
                                    <span class="text-muted small py-1 px-2   rounded">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} px-3 py-2 rounded-pill">
                                    {{ __("messages.{$appointment->status}") }}
                                </span>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-soft-info btn-sm" title="View Details" data-i18n-title="viewDetails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-soft-primary btn-sm" title="Edit" data-i18n-title="edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm(window.translations[document.documentElement.lang || 'en'].confirmCancelAppt)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" title="Delete" data-i18n-title="delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-2 d-flex flex-column gap-1">
                                    @if($appointment->status === 'confirmed')
                                        <form action="{{ route('appointments.check-in', $appointment) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm w-100" title="Check In" data-i18n-title="checkIn">
                                                <i class="fas fa-check-square me-1"></i> {{ __('messages.checkIn') }}
                                            </button>
                                        </form>
                                    @elseif($appointment->status === 'waiting')
                                        <form action="{{ route('appointments.start', $appointment) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100" title="Start Visit">
                                                <i class="fas fa-play me-1"></i> Start Visit
                                            </button>
                                        </form>
                                    @elseif($appointment->status === 'in_progress')
                                        <button class="btn btn-info text-white btn-sm w-100" 
                                            onclick="openCompletionModal({{ $appointment->id }}, '{{ addslashes($appointment->patient->name ?? 'Unknown') }}')" 
                                            title="Complete Visit">
                                            <i class="fas fa-check-double me-1"></i> Complete
                                        </button>
                                    @elseif($appointment->status === 'completed' && !$appointment->invoice)
                                        <a href="{{ route('invoices.create-from-appointment', $appointment->id) }}" class="btn btn-warning btn-sm w-100 text-dark">
                                            <i class="fas fa-file-invoice-dollar me-1"></i> <span data-i18n="createInvoice">Create Invoice</span>
                                        </a>
                                    @endif
                                </div>
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


<!-- Doctor Completion Modal -->
<div class="modal fade" id="completionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info bg-opacity-10 border-0">
                <h5 class="modal-title fw-bold text-info" id="completionModalTitle" data-i18n="completeVisit">Complete Visit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="completionForm" action="" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold" data-i18n="finalDiagnosis">Final Diagnosis / Notes</label>
                        <textarea class="form-control" name="diagnosis" rows="3" placeholder="Enter findings, diagnosis, or notes for the patient record..." data-i18n-placeholder="diagnosisPlaceholder" required></textarea>
                        <div class="form-text" data-i18n="diagnosisHint">This will be saved to the patient's medical history.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-info text-white fw-bold">
                            <i class="fas fa-check-circle me-1"></i> <span data-i18n="confirmCloseVisit">Confirm & Close Visit</span>
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-i18n="cancel">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection



@section('scripts')
<script>
function openCompletionModal(id, patientName) {
    document.getElementById('completionModalTitle').textContent = 'Complete Visit: ' + patientName;
    document.getElementById('completionForm').action = '/appointments/' + id + '/complete';
    new bootstrap.Modal(document.getElementById('completionModal')).show();
}
</script>
@endsection
