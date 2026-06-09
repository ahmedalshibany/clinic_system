@extends('layouts.dashboard')
@section('title', __('messages.patients_report'))
@section('page-title', __('messages.patients_report'))
@section('page-i18n', 'patients_report')
@section('content')
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mb-3">
    <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-1"></i> {{ __('messages.backToReports') }}
</a>
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header"><h6>{{ __('messages.genderDistribution') }}</h6></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($gender_stats as $gender => $count)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ __('messages.' . strtolower($gender)) }}
                        <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">{{ __('messages.noTransactions') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header"><h6>{{ __('messages.ageGroups') }}</h6></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($age_groups as $group => $count)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ __('messages.' . strtolower($group)) }}
                        <span class="badge bg-info rounded-pill">{{ $count }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">{{ __('messages.noTransactions') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6>{{ __('messages.recentRegistrations') }}</h6></div>
            <div class="card-body">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>{{ __('messages.nameColumn') }}</th><th>{{ __('messages.genderColumn') }}</th><th>{{ __('messages.ageColumn') }}</th><th>{{ __('messages.registeredColumn') }}</th></tr></thead>
                    <tbody>
                        @forelse($patients->take(10) as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ __('messages.' . strtolower($p->gender)) }}</td>
                            <td>{{ $p->age }}</td>
                            <td>{{ $p->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">{{ __('messages.noTransactions') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
