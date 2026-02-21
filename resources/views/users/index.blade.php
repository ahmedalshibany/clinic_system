@extends('layouts.dashboard')

@section('title', 'Users')
@section('page-title', 'User Management')
@section('page-i18n', 'users')

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
    <form action="{{ route('users.index') }}" method="GET" class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Search users..."
            value="{{ request('search') }}" data-i18n-placeholder="searchUsers">
    </form>

    <a href="{{ route('users.create') }}" class="btn btn-primary d-flex align-items-center gap-2 ms-auto">
        <i class="fas fa-plus"></i>
        <span data-i18n="addUser">Add User</span>
    </a>
</div>

<!-- Users Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">#</th>
                        <th class="py-3">Name</th>
                        <th class="py-3">Username</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">Role</th>
                        <th class="pe-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <span class="fw-bold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $user->role === 'admin' ? 'bg-danger-subtle text-danger' : ($user->role === 'doctor' ? 'bg-info-subtle text-info' : 'bg-success-subtle text-success') }} text-capitalize">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-soft-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->id() !== $user->id)
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
                                    <i class="fas fa-users"></i>
                                    <h5 data-i18n="noUsers">No users yet</h5>
                                    <p>Click "Add User" to create one.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="p-3">
             {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
