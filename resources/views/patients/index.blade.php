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

    <button class="btn btn-primary d-flex align-items-center gap-2 ms-auto" data-bs-toggle="modal" data-bs-target="#patientModal">
        <i class="fas fa-plus"></i>
        <span data-i18n="addPatient">Add Patient</span>
    </button>
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
                            <td class="ps-4 fw-bold text-secondary"><span dir="ltr">{{ $patient->id }}</span></td>
                            <td>
                                <h6 class="mb-0 fw-bold text-dark">{{ $patient->name }}</h6>
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
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-soft-primary btn-sm" onclick="editPatient({{ $patient->id }}, '{{ addslashes($patient->name) }}', {{ $patient->age }}, '{{ $patient->gender }}', '{{ addslashes($patient->phone) }}', '{{ addslashes($patient->address) }}')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('patients.destroy', $patient) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this patient?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" title="Delete">
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
                                    <p>Click "Add Patient" to get started!</p>
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
                Showing <strong>{{ $patients->firstItem() }}-{{ $patients->lastItem() }}</strong> of <strong>{{ $patients->total() }}</strong> patients
            </div>
            <div class="pagination-buttons">
                @if($patients->onFirstPage())
                    <button disabled><i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span></button>
                @else
                    <a href="{{ $patients->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> Previous</a>
                @endif
                
                @if($patients->hasMorePages())
                    <a href="{{ $patients->nextPageUrl() }}" class="btn btn-light btn-sm">Next <i class="fas fa-chevron-right"></i></a>
                @else
                    <button disabled><span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add/Edit Patient Modal -->
<div class="modal fade" id="patientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-glass border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="patientModalTitle" data-i18n="addPatient">Add Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="patientForm" action="{{ route('patients.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="mb-3">
                        <label class="form-label" data-i18n="fullName">Full Name</label>
                        <input type="text" class="form-control" name="name" id="patientName" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="age">Age</label>
                            <input type="number" class="form-control" name="age" id="patientAge" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="gender">Gender</label>
                            <select class="form-select" name="gender" id="patientGender" required>
                                <option value="male" data-i18n="male">Male</option>
                                <option value="female" data-i18n="female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" data-i18n="phone">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" id="patientPhone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" data-i18n="address">Address</label>
                        <textarea class="form-control" name="address" id="patientAddress" rows="2"></textarea>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary" data-i18n="save">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editPatient(id, name, age, gender, phone, address) {
    document.getElementById('patientModalTitle').textContent = 'Edit Patient';
    document.getElementById('patientForm').action = '/patients/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('patientName').value = name;
    document.getElementById('patientAge').value = age;
    document.getElementById('patientGender').value = gender;
    document.getElementById('patientPhone').value = phone;
    document.getElementById('patientAddress').value = address;
    new bootstrap.Modal(document.getElementById('patientModal')).show();
}

// Reset form when modal is closed
document.getElementById('patientModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('patientModalTitle').textContent = 'Add Patient';
    document.getElementById('patientForm').action = '{{ route("patients.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('patientForm').reset();
});
</script>
@endsection
