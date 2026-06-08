@extends('layouts.dashboard')

@section('title', __('messages.doctors'))
@section('page-title', __('messages.doctors'))
@section('page-i18n', 'doctors')

@section('content')
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        @foreach($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="action-toolbar d-flex gap-3 flex-wrap align-items-center justify-content-between mb-4 fade-in">
    <form action="{{ route('doctors.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control" placeholder="{{ __('messages.searchPatients') }}"
                value="{{ request('search') }}" data-i18n-placeholder="searchPatients">
        </div>
        <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
            <option value="" {{ request('status') == '' ? 'selected' : '' }} data-i18n="allStatus">{{ __('messages.allStatus') }}</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }} data-i18n="active">{{ __('messages.active') }}</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }} data-i18n="inactive">{{ __('messages.inactive') }}</option>
        </select>
    </form>
    <a href="{{ route('doctors.create') }}" class="btn btn-primary d-flex align-items-center gap-2 ms-auto">
        <i class="fas fa-plus"></i>
        <span data-i18n="addDoctor">{{ __('messages.addDoctor') }}</span>
    </a>
</div>

<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="">
                    <tr>
                        <th class="ps-4 py-3" data-i18n="doctor">{{ __('messages.doctor') }}</th>
                        <th class="py-3" data-i18n="specialty">{{ __('messages.specialty') }}</th>
                        <th class="py-3" data-i18n="department">{{ __('messages.department') }}</th>
                        <th class="py-3" data-i18n="phone">{{ __('messages.phone') }}</th>
                        <th class="py-3" data-i18n="fee">{{ __('messages.fee') }}</th>
                        <th class="py-3" data-i18n="status">{{ __('messages.status') }}</th>
                        <th class="pe-4 py-3 text-center" data-i18n="actions">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('doctors.show', $doctor) }}" class="text-dark text-decoration-none hover-primary">
                                <h6 class="mb-0 fw-bold">{{ $doctor->name }}</h6>
                            </a>
                        </td>
                        <td><span class="fw-medium">{{ $doctor->specialty }}</span></td>
                        <td>{{ $doctor->department ?? '-' }}</td>
                        <td><span dir="ltr">{{ $doctor->phone }}</span></td>
                        <td class="fw-bold">${{ number_format($doctor->consultation_fee ?? 0, 2) }}</td>
                        <td>
                            @if($doctor->is_active)
                                <span class="badge bg-success-subtle text-success" data-i18n="active">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary" data-i18n="inactive">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td class="pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-soft-info btn-sm" title="{{ __('messages.viewProfile') }}" data-i18n-title="viewProfile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('doctors.schedule', $doctor) }}" class="btn btn-soft-info btn-sm" title="{{ __('messages.manageSchedule') }}" data-i18n-title="manageSchedule">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                                <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-soft-primary btn-sm" title="{{ __('messages.edit') }}" data-i18n-title="edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('doctors.destroy', $doctor) }}" method="POST" class="d-inline" onsubmit="return confirm(window.translations[document.documentElement.lang || 'en'].confirmDeleteDoctor)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm" title="{{ __('messages.delete') }}" data-i18n-title="delete">
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
                                <i class="fas fa-user-md"></i>
                                <h5 data-i18n="noDoctors">{{ __('messages.noDoctors') }}</h5>
                                <p class="text-muted" data-i18n="clickToAddDoctor">{{ __('messages.clickToAddDoctor') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($doctors->hasPages())
        <div class="pagination-controls">
            <div class="pagination-info">
                <span data-i18n="showing">{{ __('messages.showing') }}</span> <strong>{{ $doctors->firstItem() }}-{{ $doctors->lastItem() }}</strong> <span data-i18n="of">{{ __('messages.of') }}</span> <strong>{{ $doctors->total() }}</strong> <span data-i18n="doctorsLabel">doctors</span>
            </div>
            <div class="pagination-buttons">
                @if($doctors->onFirstPage())
                    <button disabled><i class="fas fa-chevron-left"></i> <span data-i18n="previous">{{ __('messages.previous') }}</span></button>
                @else
                    <a href="{{ $doctors->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> <span data-i18n="previous">{{ __('messages.previous') }}</span></a>
                @endif
                @if($doctors->hasMorePages())
                    <a href="{{ $doctors->nextPageUrl() }}" class="btn btn-light btn-sm"><span data-i18n="next">{{ __('messages.next') }}</span> <i class="fas fa-chevron-right"></i></a>
                @else
                    <button disabled><span data-i18n="next">{{ __('messages.next') }}</span> <i class="fas fa-chevron-right"></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
