@extends('layouts.dashboard')

@section('title', 'Patients')
@section('page-title', 'Patients')
@section('page-i18n', 'patients')

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
    <form action="{{ route('patients.index') }}" method="GET" class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Search patients..."
            value="{{ request('search') }}" data-i18n-placeholder="searchPatients">
    </form>

    <a href="{{ route('patients.create') }}" class="btn btn-primary d-flex align-items-center gap-2 ms-auto">
        <i class="fas fa-plus"></i>
        <span data-i18n="addPatient">Add Patient</span>
    </a>
</div>

<!-- Patients Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">
                            <a href="{{ route('patients.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                # <i class="fas fa-sort{{ request('sort') == 'id' ? (request('direction') == 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th class="py-3">
                            <a href="{{ route('patients.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                <span data-i18n="patientName">Patient Name</span> <i class="fas fa-sort{{ request('sort') == 'name' ? (request('direction') == 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th class="py-3">
                            <a href="{{ route('patients.index', array_merge(request()->query(), ['sort' => 'age', 'direction' => request('sort') == 'age' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                <span data-i18n="age">Age</span> <i class="fas fa-sort{{ request('sort') == 'age' ? (request('direction') == 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th class="py-3" data-i18n="gender">Gender</th>
                        <th class="py-3" data-i18n="phone">Phone Number</th>
                        <th class="py-3" data-i18n="address">Address</th>
                        <th class="pe-4 py-3 text-center" data-i18n="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td class="ps-4 fw-bold text-secondary"><span dir="ltr">{{ $patient->patient_code ?? $patient->id }}</span></td>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="text-dark text-decoration-none hover-primary">
                                    <h6 class="mb-0 fw-bold">{{ $patient->name }}</h6>
                                </a>
                            </td>
                            <td>{{ $patient->age }}</td>
                            <td>
                                <span class="badge {{ $patient->gender === 'male' ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger' }} text-capitalize" data-i18n="{{ $patient->gender }}">
                                    {{ $patient->gender }}
                                </span>
                            </td>
                            <td><span dir="ltr">{{ $patient->phone }}</span></td>
                            <td>{{ $patient->address ?? '-' }}</td>
                            <td class="pe-4">
                                    <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-soft-info btn-sm" title="View Profile" data-i18n-title="viewProfile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-soft-primary btn-sm" title="Edit" data-i18n-title="edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this patient?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" title="Delete" data-i18n-title="delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-user-injured"></i>
                                    <h5 data-i18n="noPatients">No patients yet</h5>
                                    <p data-i18n="clickToAddPatient">Click "Add Patient" to get started!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($patients->hasPages())
        <div class="pagination-controls">
            <div class="pagination-info">
                <span data-i18n="showing">Showing</span> <strong>{{ $patients->firstItem() }}-{{ $patients->lastItem() }}</strong> <span data-i18n="of">of</span> <strong>{{ $patients->total() }}</strong> <span data-i18n="patientsLabel">patients</span>
            </div>
            <div class="pagination-buttons">
                @if($patients->onFirstPage())
                    <button disabled><i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span></button>
                @else
                    <a href="{{ $patients->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span></a>
                @endif
                
                @if($patients->hasMorePages())
                    <a href="{{ $patients->nextPageUrl() }}" class="btn btn-light btn-sm"><span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i></a>
                @else
                    <button disabled><span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

