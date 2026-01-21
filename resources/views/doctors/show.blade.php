@extends('layouts.dashboard')

@section('title', $doctor->name)
@section('page-title', 'Doctor Profile')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-4">
                <div class="avatar-circle bg-primary-soft text-primary mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    {{ substr($doctor->name, 0, 1) }}
                </div>
                <h5 class="mb-1">{{ $doctor->name }}</h5>
                <p class="text-muted mb-3">{{ $doctor->specialty }}</p>
                
                <div class="Badge {{ $doctor->is_active ? 'badge bg-success-subtle text-success' : 'badge bg-danger-subtle text-danger' }} mb-4">
                    {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i> Edit Profile
                    </a>
                </div>
            </div>
            <div class="card-footer bg-white p-4">
                <h6 class="text-uppercase text-muted small fw-bold mb-3">Contact Info</h6>
                <div class="d-flex align-items-center mb-3">
                    <div class="text-primary me-3"><i class="bi bi-telephone"></i></div>
                    <div>{{ $doctor->phone }}</div>
                </div>
                @if($doctor->email)
                <div class="d-flex align-items-center mb-3">
                    <div class="text-primary me-3"><i class="bi bi-envelope"></i></div>
                    <div>{{ $doctor->email }}</div>
                </div>
                @endif
                
                <h6 class="text-uppercase text-muted small fw-bold mb-3 mt-4">Practice Info</h6>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Fee:</span>
                    <span class="fw-bold">${{ number_format($doctor->consultation_fee, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Patients:</span>
                    <span class="fw-bold">{{ $doctor->appointments->unique('patient_id')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs card-header-tabs m-0" id="doctorTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active border-0 border-bottom border-primary py-3 px-4" id="bio-tab" data-bs-toggle="tab" data-bs-target="#bio" type="button" role="tab">Overview</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link border-0 py-3 px-4" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">Appointments</button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="doctorTabsContent">
                    {{-- Bio Tab --}}
                    <div class="tab-pane fade show active" id="bio" role="tabpanel">
                        <h6 class="fw-bold mb-3">Biography</h6>
                        <p class="text-muted mb-4">{{ $doctor->bio ?? 'No biography provided.' }}</p>

                        <h6 class="fw-bold mb-3">Working Hours</h6>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            @if(!empty($doctor->working_days))
                                @foreach($doctor->working_days as $day)
                                    <span class="badge bg-light text-dark border">{{ $day }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">No working days configured.</span>
                            @endif
                        </div>
                        
                        @if($doctor->work_start_time && $doctor->work_end_time)
                            <div class="alert alert-info py-2 px-3 d-inline-block">
                                <i class="bi bi-clock me-2"></i> 
                                Shifts: {{ date('h:i A', strtotime($doctor->work_start_time)) }} - {{ date('h:i A', strtotime($doctor->work_end_time)) }}
                            </div>
                        @endif
                    </div>

                    {{-- Appointments Tab --}}
                    <div class="tab-pane fade" id="appointments" role="tabpanel">
                        <h6 class="fw-bold mb-3">Recent Appointments</h6>
                        @if($doctor->appointments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Patient</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($doctor->appointments->sortByDesc('date')->take(10) as $appt)
                                            <tr>
                                                <td>
                                                    {{ $appt->date->format('M d, Y') }}<br>
                                                    <small class="text-muted">{{ $appt->time->format('h:i A') }}</small>
                                                </td>
                                                <td>{{ $appt->patient->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $appt->status == 'completed' ? 'success' : ($appt->status == 'confirmed' ? 'primary' : 'secondary') }}">
                                                        {{ ucfirst($appt->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 opacity-50"></i>
                                <p class="mt-2">No appointments found.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
