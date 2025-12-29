@extends('layouts.dashboard')

@section('title', 'Doctors')
@section('page-title', 'Doctors')
@section('page-i18n', 'doctors')

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
    <form action="{{ route('doctors.index') }}" method="GET" class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Search doctors..."
            value="{{ request('search') }}" data-i18n-placeholder="searchDoctors">
    </form>

    <button class="btn btn-primary d-flex align-items-center gap-2 ms-auto" data-bs-toggle="modal" data-bs-target="#doctorModal">
        <i class="fas fa-plus"></i>
        <span data-i18n="addDoctor">Add Doctor</span>
    </button>
</div>

<!-- Doctors Grid -->
<div class="row g-4 fade-in" id="doctorsGrid">
    @forelse($doctors as $doctor)
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card doctor-card h-100">
                <div class="card-body text-center p-4">
                    <div class="doctor-avatar mb-3">
                        @if($doctor->avatar)
                            <img src="{{ $doctor->avatar }}" alt="{{ $doctor->name }}">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($doctor->name) }}&background=0D8ABC&color=fff" alt="{{ $doctor->name }}">
                        @endif
                        <div class="status-indicator {{ $doctor->is_active ? '' : 'bg-secondary' }}"></div>
                    </div>
                    <h5 class="fw-bold text-primary mb-1">{{ $doctor->name }}</h5>
                    <p class="text-secondary small mb-3">{{ $doctor->specialty }}</p>
                    <p class="text-muted small mb-3" dir="ltr">{{ $doctor->phone }}</p>

                    <div class="action-buttons d-flex justify-content-center gap-3">
                        <button class="btn btn-soft-primary btn-sm" onclick="editDoctor({{ $doctor->id }}, '{{ addslashes($doctor->name) }}', '{{ addslashes($doctor->specialty) }}', '{{ addslashes($doctor->phone) }}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('doctors.destroy', $doctor) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this doctor?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-soft-danger btn-sm" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="empty-state text-center py-5">
                <i class="fas fa-user-md mb-3" style="font-size: 3rem;"></i>
                <h5 data-i18n="noDoctors">No doctors yet</h5>
                <p class="text-muted">Click "Add Doctor" to get started!</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($doctors->hasPages())
<div class="pagination-controls mt-4">
    <div class="pagination-info">
        Showing <strong>{{ $doctors->firstItem() }}-{{ $doctors->lastItem() }}</strong> of <strong>{{ $doctors->total() }}</strong> doctors
    </div>
    <div class="d-flex gap-2">
        @if(!$doctors->onFirstPage())
            <a href="{{ $doctors->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> Previous</a>
        @endif
        @if($doctors->hasMorePages())
            <a href="{{ $doctors->nextPageUrl() }}" class="btn btn-light btn-sm">Next <i class="fas fa-chevron-right"></i></a>
        @endif
    </div>
</div>
@endif

<!-- Add/Edit Doctor Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-glass border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="doctorModalTitle" data-i18n="addDoctor">Add Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="doctorForm" action="{{ route('doctors.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="mb-3">
                        <label class="form-label" data-i18n="fullName">Full Name</label>
                        <input type="text" class="form-control" name="name" id="doctorName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" data-i18n="specialty">Specialty</label>
                        <select class="form-select" name="specialty" id="doctorSpecialty" required>
                            <option value="General Practice">General Practice</option>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Dermatology">Dermatology</option>
                            <option value="Pediatrics">Pediatrics</option>
                            <option value="Orthopedics">Orthopedics</option>
                            <option value="Neurology">Neurology</option>
                            <option value="Ophthalmology">Ophthalmology</option>
                            <option value="ENT">ENT</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" data-i18n="phone">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" id="doctorPhone" required>
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
function editDoctor(id, name, specialty, phone) {
    document.getElementById('doctorModalTitle').textContent = 'Edit Doctor';
    document.getElementById('doctorForm').action = '/doctors/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('doctorName').value = name;
    document.getElementById('doctorSpecialty').value = specialty;
    document.getElementById('doctorPhone').value = phone;
    new bootstrap.Modal(document.getElementById('doctorModal')).show();
}

// Reset form when modal is closed
document.getElementById('doctorModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('doctorModalTitle').textContent = 'Add Doctor';
    document.getElementById('doctorForm').action = '{{ route("doctors.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('doctorForm').reset();
});
</script>
@endsection
