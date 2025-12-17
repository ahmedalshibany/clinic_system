@extends('layouts.dashboard')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-i18n', 'settings')

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card fade-in">
            <div class="card-body">
                <h5 class="mb-4 fw-bold" data-i18n="changeUser">Change Username</h5>
                <form>
                    <div class="mb-3">
                        <label class="form-label" data-i18n="newUsername">New Username</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" data-i18n="save">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card fade-in" style="animation-delay: 0.1s;">
            <div class="card-body">
                <h5 class="mb-4 fw-bold" data-i18n="changePass">Change Password</h5>
                <form>
                    <div class="mb-3">
                        <label class="form-label" data-i18n="newPassword">New Password</label>
                        <input type="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" data-i18n="save">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
