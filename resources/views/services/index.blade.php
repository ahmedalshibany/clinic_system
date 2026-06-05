@extends('layouts.dashboard')

@section('title', 'Services')
@section('page-title', 'Services')
@section('page-i18n', 'services')

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

<!-- Action Toolbar -->
<div class="action-toolbar d-flex gap-3 flex-wrap align-items-center justify-content-between mb-4 fade-in">
    <form action="{{ route('services.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control" placeholder="Search code or name..." value="{{ request('search') }}" data-i18n-placeholder="searchServices">
        </div>

        <select name="category" class="form-select" style="width: auto;" onchange="this.form.submit()">
            <option value="" data-i18n="allCategories">All Categories</option>
            <option value="consultation" {{ request('category') == 'consultation' ? 'selected' : '' }} data-i18n="catConsultation">Consultation</option>
            <option value="procedure" {{ request('category') == 'procedure' ? 'selected' : '' }} data-i18n="catProcedure">Procedure</option>
            <option value="lab" {{ request('category') == 'lab' ? 'selected' : '' }} data-i18n="catLab">Lab</option>
            <option value="imaging" {{ request('category') == 'imaging' ? 'selected' : '' }} data-i18n="catImaging">Imaging</option>
            <option value="other" {{ request('category') == 'other' ? 'selected' : '' }} data-i18n="catOther">Other</option>
        </select>
    </form>

    <button type="button" class="btn btn-primary d-flex align-items-center gap-2 ms-auto" data-bs-toggle="modal" data-bs-target="#addServiceModal">
        <i class="fas fa-plus"></i>
        <span data-i18n="addService">Add Service</span>
    </button>
</div>

<!-- Services Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="">
                    <tr>
                        <th class="ps-4 py-3" data-i18n="serviceCode">Code</th>
                        <th class="py-3" data-i18n="name">Name</th>
                        <th class="py-3" data-i18n="category">Category</th>
                        <th class="py-3" data-i18n="price">Price</th>
                        <th class="py-3" data-i18n="status">Status</th>
                        <th class="pe-4 py-3 text-center" data-i18n="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td class="ps-4 fw-bold text-secondary">{{ $service->code }}</td>
                        <td>
                            <span class="fw-medium">{{ $service->name }}</span>
                            @if($service->name_ar)
                                <br><small class="text-muted">{{ $service->name_ar }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary text-uppercase">{{ $service->category }}</span></td>
                        <td class="fw-bold">{{ number_format($service->price, 2) }}</td>
                        <td>
                            @if($service->is_active)
                                <span class="badge bg-success-subtle text-success" data-i18n="active">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary" data-i18n="inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-soft-primary btn-sm" 
                                        onclick="editService({{ $service->toJson() }})"
                                        data-bs-toggle="modal" data-bs-target="#editServiceModal"
                                        title="Edit" data-i18n-title="edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm(window.translations[document.documentElement.lang || 'en'].confirmDeleteService)">
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
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-briefcase-medical text-muted mb-3" style="font-size: 2rem;"></i>
                                <h5 data-i18n="noServices">No services found.</h5>
                                <p class="text-muted small mt-2" data-i18n="clickToAddService">Click "Add Service" to get started!</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
        <div class="pagination-controls">
            <div class="pagination-info">
                <span data-i18n="showing">Showing</span> <strong>{{ $services->firstItem() }}-{{ $services->lastItem() }}</strong> <span data-i18n="of">of</span> <strong>{{ $services->total() }}</strong> <span data-i18n="servicesLabel">services</span>
            </div>
            <div class="pagination-buttons">
                @if($services->onFirstPage())
                    <button disabled><i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span></button>
                @else
                    <a href="{{ $services->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span></a>
                @endif
                
                @if($services->hasMorePages())
                    <a href="{{ $services->nextPageUrl() }}" class="btn btn-light btn-sm"><span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i></a>
                @else
                    <button disabled><span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('services.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" data-i18n="addService">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" data-i18n="serviceCode">Service Code</label>
                    <input type="text" name="code" class="form-control" required placeholder="e.g. CON-001" data-i18n-placeholder="serviceCodePlaceholder">
                </div>
                <div class="mb-3">
                    <label class="form-label" data-i18n="nameEn">Name (English)</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" data-i18n="nameAr">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" dir="rtl">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label" data-i18n="category">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="consultation" data-i18n="catConsultation">Consultation</option>
                            <option value="procedure" data-i18n="catProcedure">Procedure</option>
                            <option value="lab" data-i18n="catLab">Lab</option>
                            <option value="imaging" data-i18n="catImaging">Imaging</option>
                            <option value="other" data-i18n="catOther">Other</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" data-i18n="price">Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price" class="form-control" required>
                            <span class="input-group-text">$</span>
                        </div>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                    <label class="form-check-label" data-i18n="activeService">Active Service</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-i18n="cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" data-i18n="saveService">Save Service</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="#" method="POST" id="editForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" data-i18n="editService">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" data-i18n="serviceCode">Service Code</label>
                    <input type="text" name="code" id="edit_code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" data-i18n="nameEn">Name (English)</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" data-i18n="nameAr">Name (Arabic)</label>
                    <input type="text" name="name_ar" id="edit_name_ar" class="form-control" dir="rtl">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label" data-i18n="category">Category</label>
                        <select name="category" id="edit_category" class="form-select" required>
                            <option value="consultation" data-i18n="catConsultation">Consultation</option>
                            <option value="procedure" data-i18n="catProcedure">Procedure</option>
                            <option value="lab" data-i18n="catLab">Lab</option>
                            <option value="imaging" data-i18n="catImaging">Imaging</option>
                            <option value="other" data-i18n="catOther">Other</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" data-i18n="price">Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                            <span class="input-group-text">$</span>
                        </div>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                    <label class="form-check-label" data-i18n="activeService">Active Service</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-i18n="cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" data-i18n="updateService">Update Service</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editService(service) {
        document.getElementById('editForm').action = `/services/${service.id}`;
        document.getElementById('edit_code').value = service.code;
        document.getElementById('edit_name').value = service.name;
        document.getElementById('edit_name_ar').value = service.name_ar || '';
        document.getElementById('edit_category').value = service.category;
        document.getElementById('edit_price').value = service.price;
        document.getElementById('edit_is_active').checked = service.is_active;
    }
</script>
@endsection
