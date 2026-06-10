@extends('layouts.dashboard')

@section('title', __('messages.users') . ' / ' . __('messages.edit'))
@section('page-title', __('messages.users') . ' / ' . __('messages.edit'))
@section('page-i18n', 'users')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.name') }}</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.username') }}</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.email') }}</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.phone') }}</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.role') }}</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('messages.role_admin') }}</option>
                            <option value="doctor" {{ old('role', $user->role) == 'doctor' ? 'selected' : '' }}>{{ __('messages.role_doctor') }}</option>
                            <option value="receptionist" {{ old('role', $user->role) == 'receptionist' ? 'selected' : '' }}>{{ __('messages.role_receptionist') }}</option>
                            <option value="nurse" {{ old('role', $user->role) == 'nurse' ? 'selected' : '' }}>{{ __('messages.role_nurse') }}</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.password') }} <small class="text-muted">({{ __('messages.leaveEmptyToKeep') }})</small></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">{{ __('messages.active') }}</label>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ smartBack('users.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4">{{ __('messages.updateUser') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
