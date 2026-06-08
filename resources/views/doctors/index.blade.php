@extends('layouts.dashboard')

@section('title', __('messages.doctors'))
@section('page-title', __('messages.doctors'))
@section('page-i18n', 'doctors')

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
    <form action="{{ route('doctors.index') }}" method="GET" class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Search doctors..."
            value="{{ request('search') }}" data-i18n-placeholder="searchDoctors">
    </form>

    <a href="{{ route('doctors.create') }}" class="btn btn-primary d-flex align-items-center gap-2 ms-auto">
        <i class="fas fa-plus"></i>
        <span data-i18n="addDoctor">Add Doctor</span>
    </a>
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
                        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-soft-info btn-sm" title="View Profile" data-i18n-title="viewProfile">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('doctors.schedule', $doctor) }}" class="btn btn-soft-info btn-sm" title="Manage Schedule" data-i18n-title="manageSchedule">
                            <i class="fas fa-calendar-alt"></i>
                        </a>
                        <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-soft-primary btn-sm" title="Edit" data-i18n-title="edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('doctors.destroy', $doctor) }}" method="POST" class="d-inline" onsubmit="return confirm(window.translations[document.documentElement.lang || 'en'].confirmDeleteDoctor)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-soft-danger btn-sm" title="Delete" data-i18n-title="delete">
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
                <i class="fas fa-user-md mb-3 icon-xl"></i>
                <h5 data-i18n="noDoctors">No doctors yet</h5>
                <p class="text-muted" data-i18n="clickToAddDoctor">Click "Add Doctor" to get started!</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($doctors->hasPages())
<div class="pagination-controls mt-4">
    <div class="pagination-info">
        <span data-i18n="showing">Showing</span> <strong>{{ $doctors->firstItem() }}-{{ $doctors->lastItem() }}</strong> <span data-i18n="of">of</span> <strong>{{ $doctors->total() }}</strong> <span data-i18n="doctorsLabel">doctors</span>
    </div>
    <div class="d-flex gap-2">
        @if(!$doctors->onFirstPage())
            <a href="{{ $doctors->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span></a>
        @endif
        @if($doctors->hasMorePages())
            <a href="{{ $doctors->nextPageUrl() }}" class="btn btn-light btn-sm"><span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i></a>
        @endif
    </div>
</div>
@endif


@endsection


