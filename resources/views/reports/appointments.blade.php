@extends('layouts.dashboard')
@section('title', 'Appointments Report')
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between">
        <h5 class="mb-0">Appointments</h5>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            @foreach($status_stats as $status => $count)
            <div class="col-md-2 text-center">
                <h3 class="mb-0">{{ $count }}</h3>
                <small class="text-uppercase text-muted">{{ $status }}</small>
            </div>
            @endforeach
        </div>
        <table class="table table-hover">
            <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($appointments as $appt)
                <tr>
                    <td>{{ $appt->date->format('M d, Y') }} {{ $appt->time->format('H:i') }}</td>
                    <td>{{ $appt->patient->name }}</td>
                    <td>{{ $appt->doctor->user->name }}</td>
                    <td><span class="badge bg-secondary">{{ $appt->status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
