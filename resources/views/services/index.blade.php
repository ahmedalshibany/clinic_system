@extends('layouts.dashboard')

@section('title', 'Services Management')
@section('page-title', 'Services Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Clinic Services</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="fas fa-plus me-2"></i> Add Service
                </button>
            </div>
            <div class="card-body">
                {{-- Filters --}}
                <form action="{{ route('services.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search code or name..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <option value="consultation" {{ request('category') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                            <option value="procedure" {{ request('category') == 'procedure' ? 'selected' : '' }}>Procedure</option>
                            <option value="lab" {{ request('category') == 'lab' ? 'selected' : '' }}>Lab</option>
                            <option value="imaging" {{ request('category') == 'imaging' ? 'selected' : '' }}>Imaging</option>
                            <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                            <tr>
                                <td class="fw-bold text-primary">{{ $service->code }}</td>
                                <td>
                                    <div>{{ $service->name }}</div>
                                    @if($service->name_ar)
                                        <small class="text-muted">{{ $service->name_ar }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary text-uppercase">{{ $service->category }}</span></td>
                                <td class="fw-bold">{{ number_format($service->price, 2) }}</td>
                                <td>
                                    @if($service->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary me-1 text-nowrap" 
                                            onclick="editService({{ $service->toJson() }})"
                                            data-bs-toggle="modal" data-bs-target="#editServiceModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No services found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $services->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('services.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Service Code</label>
                    <input type="text" name="code" class="form-control" required placeholder="e.g. CON-001">
                </div>
                <div class="mb-3">
                    <label class="form-label">Name (English)</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" dir="rtl">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="consultation">Consultation</option>
                            <option value="procedure">Procedure</option>
                            <option value="lab">Lab</option>
                            <option value="imaging">Imaging</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price" class="form-control" required>
                            <span class="input-group-text">$</span>
                        </div>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                    <label class="form-check-label">Active Service</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Service</button>
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
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Service Code</label>
                    <input type="text" name="code" id="edit_code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Name (English)</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" id="edit_name_ar" class="form-control" dir="rtl">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Category</label>
                        <select name="category" id="edit_category" class="form-select" required>
                            <option value="consultation">Consultation</option>
                            <option value="procedure">Procedure</option>
                            <option value="lab">Lab</option>
                            <option value="imaging">Imaging</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                            <span class="input-group-text">$</span>
                        </div>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                    <label class="form-check-label">Active Service</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Service</button>
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
