@extends('layouts.dashboard')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-i18n', 'userManagement')

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
    <form action="{{ route('users.index') }}" method="GET" class="d-flex gap-2 flex-wrap align-items-center">
        <!-- Search Box -->
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control" placeholder="Search users..."
                value="{{ request('search') }}" data-i18n-placeholder="searchUsers">
        </div>

        <!-- Role Filter -->
        <select name="role" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
            <option value="" data-i18n="allRoles">All Roles</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }} data-i18n="admin">Administrator</option>
            <option value="doctor" {{ request('role') == 'doctor' ? 'selected' : '' }} data-i18n="doctor">Doctor</option>
            <option value="receptionist" {{ request('role') == 'receptionist' ? 'selected' : '' }} data-i18n="receptionist">Receptionist</option>
        </select>

        <!-- Status Filter -->
        <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
            <option value="" data-i18n="allStatus">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }} data-i18n="active">Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }} data-i18n="inactive">Inactive</option>
        </select>
    </form>

    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetUserForm()">
        <i class="fas fa-plus"></i>
        <span data-i18n="addUser">Add User</span>
    </button>
</div>

<!-- Users Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">
                            <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                # <i class="fas fa-sort{{ request('sort') == 'id' ? (request('direction') == 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th class="py-3">
                            <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                <span data-i18n="name">Name</span> <i class="fas fa-sort{{ request('sort') == 'name' ? (request('direction') == 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th class="py-3">
                            <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'username', 'direction' => request('sort') == 'username' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                <span data-i18n="username">Username</span> <i class="fas fa-sort{{ request('sort') == 'username' ? (request('direction') == 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th class="py-3" data-i18n="role">Role</th>
                        <th class="py-3" data-i18n="status">Status</th>
                        <th class="pe-4 py-3 text-center" data-i18n="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4 fw-bold text-secondary"><span dir="ltr">{{ $user->id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email ?? 'No email' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code class="bg-light px-2 py-1 rounded">{{ $user->username }}</code></td>
                            <td>
                                @php
                                    $roleColors = [
                                        'admin' => 'bg-danger-subtle text-danger',
                                        'doctor' => 'bg-info-subtle text-info',
                                        'receptionist' => 'bg-success-subtle text-success',
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Administrator',
                                        'doctor' => 'Doctor',
                                        'receptionist' => 'Receptionist',
                                    ];
                                @endphp
                                <span class="badge {{ $roleColors[$user->role] ?? 'bg-secondary' }}" data-i18n="{{ $user->role }}">
                                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle text-success" data-i18n="active">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger" data-i18n="inactive">
                                        <i class="fas fa-times-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Edit Button -->
                                    <button class="btn btn-soft-primary btn-sm" 
                                        onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->username }}', '{{ $user->email }}', '{{ $user->phone }}', '{{ $user->role }}', {{ $user->is_active ? 'true' : 'false' }})" 
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Toggle Active Button -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.toggle', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-soft-{{ $user->is_active ? 'warning' : 'success' }} btn-sm" 
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                                onclick="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?')">
                                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <!-- Reset Password Button -->
                                    <form action="{{ route('users.reset-password', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-soft-info btn-sm" title="Reset Password"
                                            onclick="return confirm('Are you sure you want to reset this user\'s password?')">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </form>
                                    
                                    <!-- Delete Button -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-users-cog"></i>
                                    <h5 data-i18n="noUsers">No users found</h5>
                                    <p>Click "Add User" to create a new user!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="pagination-controls">
            <div class="pagination-info">
                Showing <strong>{{ $users->firstItem() }}-{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
            </div>
            <div class="pagination-buttons">
                @if($users->onFirstPage())
                    <button disabled><i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span></button>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> Previous</a>
                @endif
                
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="btn btn-light btn-sm">Next <i class="fas fa-chevron-right"></i></a>
                @else
                    <button disabled><span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-glass border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="userModalTitle" data-i18n="addUser">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="userForm" action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    
                    <div class="mb-3">
                        <label class="form-label" data-i18n="fullName">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="userName" required>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="username">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="userUsername" required pattern="[a-zA-Z0-9_-]+" title="Only letters, numbers, dashes and underscores">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="role">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="userRole" required>
                                <option value="admin" data-i18n="admin">Administrator</option>
                                <option value="doctor" data-i18n="doctor">Doctor</option>
                                <option value="receptionist" data-i18n="receptionist">Receptionist</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="email">Email</label>
                            <input type="email" class="form-control" name="email" id="userEmail">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="phone">Phone</label>
                            <input type="tel" class="form-control" name="phone" id="userPhone">
                        </div>
                    </div>
                    
                    <div id="passwordFields">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" data-i18n="password">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" id="userPassword" minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" data-i18n="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirmation" id="userPasswordConfirmation" minlength="8">
                            </div>
                        </div>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Password must be at least 8 characters with letters and numbers
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="userActive" value="1" checked>
                            <label class="form-check-label" for="userActive" data-i18n="activeAccount">Active Account</label>
                        </div>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary" data-i18n="save">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function resetUserForm() {
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('userForm').action = '{{ route("users.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('userForm').reset();
    document.getElementById('userActive').checked = true;
    document.getElementById('passwordFields').style.display = 'block';
    document.getElementById('userPassword').required = true;
    document.getElementById('userPasswordConfirmation').required = true;
}

function editUser(id, name, username, email, phone, role, isActive) {
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userForm').action = '{{ url("users") }}/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('userName').value = name;
    document.getElementById('userUsername').value = username;
    document.getElementById('userEmail').value = email || '';
    document.getElementById('userPhone').value = phone || '';
    document.getElementById('userRole').value = role;
    document.getElementById('userActive').checked = isActive;
    
    // Hide password fields on edit (optional change)
    document.getElementById('passwordFields').style.display = 'block';
    document.getElementById('userPassword').required = false;
    document.getElementById('userPasswordConfirmation').required = false;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPasswordConfirmation').value = '';
    
    new bootstrap.Modal(document.getElementById('userModal')).show();
}

// Reset form when modal is closed
document.getElementById('userModal').addEventListener('hidden.bs.modal', function () {
    resetUserForm();
});
</script>
@endsection
