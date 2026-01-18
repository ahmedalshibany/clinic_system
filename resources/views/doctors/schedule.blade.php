@extends('layouts.dashboard')

@section('title', 'Manage Schedule - ' . $doctor->name)
@section('page-title', 'Manage Schedule')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Weekly Schedule for {{ $doctor->name }}</h5>
                <a href="{{ route('doctors.index') }}" class="btn btn-secondary btn-sm">Back to Doctors</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('doctors.schedule.update', $doctor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px">Day</th>
                                    <th>Status</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Slot Duration (min)</th>
                                    <th>Max Patients</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                @endphp

                                @foreach($days as $index => $day)
                                    @php
                                        $schedule = $schedules[$index] ?? null;
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">
                                            {{ $day }}
                                            <input type="hidden" name="schedules[{{ $index }}][day_of_week]" value="{{ $index }}">
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="schedules[{{ $index }}][is_active]" value="1" 
                                                    {{ ($schedule && $schedule->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="time" class="form-control" name="schedules[{{ $index }}][start_time]" 
                                                value="{{ $schedule ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '09:00' }}">
                                        </td>
                                        <td>
                                            <input type="time" class="form-control" name="schedules[{{ $index }}][end_time]" 
                                                value="{{ $schedule ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '17:00' }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="schedules[{{ $index }}][slot_duration]" 
                                                value="{{ $schedule->slot_duration ?? 30 }}" min="5" max="120" step="5">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="schedules[{{ $index }}][max_appointments]" 
                                                value="{{ $schedule->max_appointments ?? '' }}" placeholder="Unlimited" min="1">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Schedule
                        </button>
                    </div>
                </form>
            </div>
                </form>
            </div>
        </div>

        <!-- Leave Management Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Leave Management</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Leaves List -->
                    <div class="col-md-7">
                        <h6 class="mb-3">Upcoming Leaves</h6>
                        @if($leaves->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Reason</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($leaves as $leave)
                                            <tr>
                                                <td>{{ $leave->start_date->format('M d, Y') }}</td>
                                                <td>{{ $leave->end_date->format('M d, Y') }}</td>
                                                <td>{{ $leave->reason ?? '-' }}</td>
                                                <td>
                                                    <form action="{{ route('doctors.leaves.destroy', [$doctor, $leave]) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No upcoming leaves scheduled.</p>
                        @endif
                    </div>

                    <!-- Add Leave Form -->
                    <div class="col-md-5">
                        <div class="bg-light p-3 rounded">
                            <h6 class="mb-3">Add New Leave</h6>
                            <form action="{{ route('doctors.leaves.store', $doctor) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" required min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date" required min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason (Optional)</label>
                                    <input type="text" class="form-control" name="reason" placeholder="e.g. Vacation, Conference">
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Add Leave
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
