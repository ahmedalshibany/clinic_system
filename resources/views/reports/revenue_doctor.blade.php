@extends('layouts.dashboard')
@section('title', 'Revenue by Doctor')
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Revenue by Doctor</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead><tr><th>Doctor</th><th>Appointments</th><th class="text-end">Total Earned</th></tr></thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td>{{ $row->doctor_name }}</td>
                    <td>{{ $row->appointment_count }}</td>
                    <td class="text-end fw-bold text-success">${{ number_format($row->total_earned, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
