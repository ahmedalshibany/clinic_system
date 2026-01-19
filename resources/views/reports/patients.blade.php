@extends('layouts.dashboard')
@section('title', 'Patient Demographics')
@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h6>Gender Distribution</h6></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($gender_stats as $gender => $count)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ ucfirst($gender) }}
                        <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h6>Age Groups</h6></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($age_groups as $group => $count)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $group }}
                        <span class="badge bg-info rounded-pill">{{ $count }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6>Recent Registrations</h6></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Name</th><th>Gender</th><th>Age</th><th>Registered</th></tr></thead>
                    <tbody>
                        @foreach($patients->take(10) as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ ucfirst($p->gender) }}</td>
                            <td>{{ $p->age }}</td>
                            <td>{{ $p->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
