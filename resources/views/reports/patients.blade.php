@extends('layouts.dashboard')

@section('page-title', __('messages.patients_report'))
@section('page-i18n', 'patients_report')

@section('content')
<style>
    .demographics-actions-row {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        margin-bottom: 1.5rem !important;
        min-height: 46px !important;
    }
    .demographics-actions-row .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 42px !important;
        border-radius: 10px !important;
        font-weight: 500 !important;
    }
    .demographics-filter-wrapper {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 1rem !important;
        width: 100% !important;
    }
    .demographics-metrics-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 16px !important;
        width: 100% !important;
        margin-bottom: 2rem !important;
    }
    .demographics-premium-card {
        background: var(--white) !important;
        border-radius: 16px !important;
        padding: 1.5rem !important;
        height: 100% !important;
        box-shadow: var(--shadow-soft, 0 4px 20px rgba(0, 0, 0, 0.02)) !important;
        border: none !important;
        text-align: center !important;
    }
    .demographics-charts-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 20px !important;
        width: 100% !important;
        margin-bottom: 2rem !important;
    }
    .demographics-metrics-grid,
    .demographics-metrics-grid *,
    .demographics-actions-row,
    .demographics-filter-wrapper,
    .demographics-charts-grid {
        transition: none !important;
        animation: none !important;
    }
    .demo-token-total { color: #153d3e !important; font-weight: 700; }
    .demo-token-male { color: #2b5c8f !important; font-weight: 700; }
    .demo-token-female { color: #b85c7b !important; font-weight: 700; }
    .demo-token-regions { color: #b07d2a !important; font-weight: 700; }
    input[type="date"].form-control-luxury {
        padding: 0.6rem 1rem !important;
        border-radius: 10px !important;
        background-color: rgba(15, 61, 62, 0.02) !important;
        border: 1px solid rgba(15, 61, 62, 0.12) !important;
        color: var(--secondary) !important;
        font-weight: 500;
    }
    .luxury-progress-track {
        background-color: rgba(0, 0, 0, 0.03) !important;
        border-radius: 8px !important;
        height: 8px !important;
        overflow: hidden !important;
    }
</style>

<div class="demographics-actions-row">
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs"></i>
        <span class="text-sm fw-medium">{{ __('messages.backToReports') }}</span>
    </a>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-light border" style="color: var(--secondary); background: var(--white);"><i class="fas fa-print ml-2"></i>{{ __('messages.print') }}</button>
        <button class="btn btn-light border" style="color: var(--secondary); background: var(--white);"><i class="fas fa-file-excel ml-2"></i>{{ __('messages.exportExcel') }}</button>
    </div>
</div>

<form action="{{ route('reports.patients') }}" method="GET" id="demographicsFilterForm" class="w-100">
    <input type="hidden" name="quick_filter" id="quickFilterInput" value="{{ request('quick_filter') }}">

    <div class="card border-0 p-4 mb-4 shadow-sm" style="background: var(--white); border-radius: 16px;">
        <div class="demographics-filter-wrapper">
            <div class="d-flex flex-wrap gap-2">
                <button type="button" onclick="applyQuickFilter('today')" class="btn btn-sm {{ request('quick_filter') == 'today' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'today' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.today') }}</button>
                <button type="button" onclick="applyQuickFilter('this_week')" class="btn btn-sm {{ request('quick_filter') == 'this_week' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'this_week' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisWeek') }}</button>
                <button type="button" onclick="applyQuickFilter('this_month')" class="btn btn-sm {{ (request('quick_filter') == 'this_month' || !request('quick_filter')) ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ (request('quick_filter') == 'this_month' || !request('quick_filter')) ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisMonth') }}</button>
                <button type="button" onclick="applyQuickFilter('this_year')" class="btn btn-sm {{ request('quick_filter') == 'this_year' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'this_year' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisYear') }}</button>
            </div>
            <div class="d-flex gap-3 justify-content-md-end mb-3 mb-md-0">
                <div style="width: 160px;">
                    <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.fromDate') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="document.getElementById('quickFilterInput').value=''; document.getElementById('demographicsFilterForm').submit();" class="form-control form-control-luxury">
                </div>
                <div style="width: 160px;">
                    <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.toDate') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" onchange="document.getElementById('quickFilterInput').value=''; document.getElementById('demographicsFilterForm').submit();" class="form-control form-control-luxury">
                </div>
            </div>
        </div>
    </div>
</form>

<div class="demographics-metrics-grid">
    <div class="demographics-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.total_patients') }}</span>
        <h3 class="demo-token-total mb-1" style="font-size: 1.8rem;">{{ number_format($total_patients_count) }}</h3>
        <small class="text-muted text-xs d-block">{{ __('messages.active_records') }}</small>
    </div>
    <div class="demographics-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.male_patients') }}</span>
        <h3 class="demo-token-male mb-1" style="font-size: 1.8rem;">{{ number_format($total_male_count) }}</h3>
        <small class="text-muted text-xs d-block">
            @if($total_patients_count > 0)
                {{ round(($total_male_count / $total_patients_count) * 100, 1) }}% {{ __('messages.ratio') }}
            @else 0% @endif
        </small>
    </div>
    <div class="demographics-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.female_patients') }}</span>
        <h3 class="demo-token-female mb-1" style="font-size: 1.8rem;">{{ number_format($total_female_count) }}</h3>
        <small class="text-muted text-xs d-block">
            @if($total_patients_count > 0)
                {{ round(($total_female_count / $total_patients_count) * 100, 1) }}% {{ __('messages.ratio') }}
            @else 0% @endif
        </small>
    </div>
    <div class="demographics-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.top_location') }}</span>
        <h3 class="demo-token-regions mb-1 text-truncate" style="font-size: 1.3rem; max-width: 100%;">
            {{ $locationQuery->first()->location_name ?? '—' }}
        </h3>
        <small class="text-muted text-xs d-block">{{ __('messages.highest_density') }}</small>
    </div>
</div>

<div class="demographics-charts-grid">
    <div class="card border-0 p-4 shadow-sm" style="border-radius: 16px; background: var(--white);">
        <h6 class="text-secondary fw-bold mb-3 pb-1 border-bottom text-sm"><i class="fas fa-venus-mars ml-2 text-muted"></i>{{ __('messages.gender_distribution') }}</h6>
        <div class="d-flex flex-column gap-3 mt-2">
            @foreach(['male' => ['#2b5c8f', 'Male'], 'female' => ['#b85c7b', 'Female']] as $key => $props)
                @php
                    $count = $gender_stats[$key] ?? 0;
                    $pct = $total_patients_count > 0 ? ($count / $total_patients_count) * 100 : 0;
                @endphp
                <div>
                    <div class="d-flex justify-content-between text-xs fw-medium mb-1">
                        <span class="text-dark">{{ __('messages.' . $key) }}</span>
                        <span class="text-muted">{{ $count }} ({{ round($pct, 1) }}%)</span>
                    </div>
                    <div class="luxury-progress-track">
                        <div class="h-100" style="width: {{ $pct }}%; background-color: {{ $props[0] }}; border-radius: 8px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card border-0 p-4 shadow-sm" style="border-radius: 16px; background: var(--white);">
        <h6 class="text-secondary fw-bold mb-3 pb-1 border-bottom text-sm"><i class="fas fa-baby-carriage ml-2 text-muted"></i>{{ __('messages.age_groups') }}</h6>
        <div class="d-flex flex-column gap-2" style="max-height: 180px; overflow-y: auto;">
            @foreach(['Child' => '#4caf50', 'Adult' => '#ff9800', 'Senior' => '#f44336', 'Unknown' => '#9e9e9e'] as $group => $color)
                @php
                    $count = $age_groups[$group] ?? 0;
                    $pct = $total_patients_count > 0 ? ($count / $total_patients_count) * 100 : 0;
                @endphp
                <div>
                    <div class="d-flex justify-content-between text-xs mb-1">
                        <span class="text-dark fw-medium">{{ __('messages.age_' . strtolower($group)) ?? $group }}</span>
                        <span class="text-muted text-xs">{{ $count }} ({{ round($pct, 0) }}%)</span>
                    </div>
                    <div class="luxury-progress-track">
                        <div class="h-100" style="width: {{ $pct }}%; background-color: {{ $color }}; border-radius: 8px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card border-0 p-4 shadow-sm" style="border-radius: 16px; background: var(--white);">
        <h6 class="text-secondary fw-bold mb-3 pb-1 border-bottom text-sm"><i class="fas fa-map-marker-alt ml-2 text-muted"></i>{{ __('messages.geo_distribution') }}</h6>
        <div class="d-flex flex-column gap-2">
            @forelse($locationQuery as $loc)
                @php $pct = $total_patients_count > 0 ? ($loc->total / $total_patients_count) * 100 : 0; @endphp
                <div>
                    <div class="d-flex justify-content-between text-xs mb-1">
                        <span class="text-dark fw-medium text-truncate" style="max-width: 65%;">{{ $loc->location_name }}</span>
                        <span class="text-muted text-xs">{{ $loc->total }} ({{ round($pct, 0) }}%)</span>
                    </div>
                    <div class="luxury-progress-track">
                        <div class="h-100" style="width: {{ $pct }}%; background-color: #b07d2a; border-radius: 8px;"></div>
                    </div>
                </div>
            @empty
                <div class="text-center py-3 text-muted text-xs fw-medium">{{ __('messages.no_data_available') }}</div>
            @endforelse
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header border-0 bg-transparent pt-4 px-4">
        <h6 class="card-title fw-bold text-dark mb-0 text-sm"><i class="fas fa-history ml-2 text-muted"></i>{{ __('messages.recent_registrations') }}</h6>
    </div>
    <div class="card-body p-4 pt-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-secondary text-xs fw-bold border-0">
                        <th class="py-3">{{ __('messages.patientColumn') }}</th>
                        <th class="py-3 text-center">{{ __('messages.genderColumn') }}</th>
                        <th class="py-3 text-center">{{ __('messages.ageColumn') }}</th>
                        <th class="py-3 text-end">{{ __('messages.registeredDateColumn') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr class="border-bottom">
                            <td class="py-3 fw-medium text-dark text-sm">
                                {{ $patient->name }}
                            </td>
                            <td class="py-3 text-center text-sm text-secondary">
                                {{ __('messages.' . strtolower($patient->gender)) ?? $patient->gender }}
                            </td>
                            <td class="py-3 text-center text-sm fw-semibold text-dark">
                                {{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : ($patient->age ?? '—') }}
                            </td>
                            <td class="py-3 text-end text-sm text-secondary">
                                {{ $patient->created_at ? $patient->created_at->format('Y-m-d') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted text-sm fw-medium">
                                {{ __('messages.no_data_available') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function applyQuickFilter(filterValue) {
    document.getElementById('quickFilterInput').value = filterValue;
    document.getElementsByName('date_from')[0].value = '';
    document.getElementsByName('date_to')[0].value = '';
    document.getElementById('demographicsFilterForm').submit();
}
</script>
@endsection
