@extends('layouts.dashboard')

@section('title', 'Users')
@section('page-title', 'Users')
@section('page-i18n', 'users')

@section('content')
<!-- Action Toolbar -->
<div class="action-toolbar d-flex gap-3 flex-wrap align-items-center justify-content-between mb-4 fade-in">
    <form action="{{ route('users.index') }}" method="GET" class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Search users..."
            value="{{ request('search') }}" data-i18n-placeholder="searchUsers">
    </form>

    <a href="{{ route('users.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 ms-auto">
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
                        <th class="ps-4 py-3" style="width: 50px;">{{ __('messages.number') }}</th>
                        <th class="py-3">{{ __('messages.name') }}</th>
                        <th class="py-3">{{ __('messages.username') }}</th>
                        <th class="py-3">{{ __('messages.email') }}</th>
                        <th class="py-3">{{ __('messages.role') }}</th>
                        <th class="pe-4 py-3 text-center">{{ __('messages.actions') }}</th>
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
                                    {{ __('messages.role_' . $user->role) }}
                                </span>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-soft-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm(window.translations[document.documentElement.lang || 'en'].confirmDeleteUser)">
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
                                    <p data-i18n="clickToAddUser">{{ __('messages.clickToAddUser') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="pagination-controls d-flex justify-content-between align-items-center p-3">
            <div class="pagination-info">
                <span data-i18n="showing">{{ __('messages.showing') }}</span> <strong>{{ $users->firstItem() }}-{{ $users->lastItem() }}</strong> <span data-i18n="of">{{ __('messages.of') }}</span> <strong>{{ $users->total() }}</strong> <span data-i18n="usersLabel">{{ __('messages.usersLabel') }}</span>
            </div>
            <div class="pagination-buttons d-flex gap-2">
                @if($users->onFirstPage())
                    <button class="btn btn-light btn-sm" disabled><i class="fas fa-chevron-left"></i> <span data-i18n="previous">{{ __('messages.previous') }}</span></button>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> <span data-i18n="previous">{{ __('messages.previous') }}</span></a>
                @endif

                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="btn btn-light btn-sm"><span data-i18n="next">{{ __('messages.next') }}</span> <i class="fas fa-chevron-right"></i></a>
                @else
                    <button class="btn btn-light btn-sm" disabled><span data-i18n="next">{{ __('messages.next') }}</span> <i class="fas fa-chevron-right"></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
