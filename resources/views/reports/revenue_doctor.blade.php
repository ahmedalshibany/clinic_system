@extends('layouts.dashboard')
@section('title', __('messages.revenue_doctor_report'))
@section('page-title', __('messages.revenue_doctor_report'))
@section('page-i18n', 'revenueDoctorReport')
@section('content')

<style>
    .form-control-luxury {
        padding: 0.6rem 1rem !important;
        border-radius: 10px !important;
        background-color: rgba(15, 61, 62, 0.02) !important;
        border: 1px solid rgba(15, 61, 62, 0.12) !important;
        color: var(--secondary) !important;
        font-weight: 500 !important;
        font-size: 0.9rem !important;
        transition: all 0.2s ease !important;
    }
    .form-control-luxury:focus {
        border-color: var(--secondary) !important;
        background-color: var(--white) !important;
        box-shadow: 0 0 0 3px rgba(15, 61, 62, 0.05) !important;
    }
    .luxury-actions-container {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        margin-bottom: 1.5rem !important;
    }
    .luxury-actions-container .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 42px !important;
        border-radius: 10px !important;
        font-weight: 500 !important;
    }
    .luxury-filter-row {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }
    .luxury-date-inputs-wrapper {
        display: flex !important;
        gap: 12px !important;
    }
    .luxury-date-field {
        width: 150px !important;
    }
    .doctor-metrics-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 16px !important;
        width: 100% !important;
        margin-bottom: 2rem !important;
    }
    .doctor-premium-card {
        background: var(--white) !important;
        border-radius: 16px !important;
        padding: 1.5rem !important;
        height: 100% !important;
        box-shadow: var(--shadow-soft, 0 4px 20px rgba(0, 0, 0, 0.02)) !important;
        border: none !important;
        text-align: center !important;
    }
    .doctor-metrics-grid,
    .doctor-metrics-grid * {
        transition: none !important;
        animation: none !important;
    }
    .doctor-num-total { color: #2e7d32 !important; font-weight: 700; }
    .doctor-num-count { color: #2e5b5c !important; font-weight: 700; }
    .doctor-num-top { color: #b07d2a !important; font-weight: 700; }
</style>

<div class="luxury-actions-container">
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary gap-2">
        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs"></i>
        <span class="text-sm fw-medium">{{ __('messages.backToReports') }}</span>
    </a>

    <div class="d-flex align-items-center gap-2">
        <button onclick="window.print()" class="btn btn-light border" style="color: var(--secondary); background: var(--white);"><i class="fas fa-print ml-2"></i>{{ __('messages.print') }}</button>
        <a href="{{ route('reports.export.excel', ['report' => 'revenue', 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" class="btn btn-light border" style="color: var(--secondary); background: var(--white);"><i class="fas fa-file-excel ml-2"></i>{{ __('messages.exportExcel') }}</a>
    </div>
</div>

<form action="{{ route('reports.revenue.doctor') }}" method="GET" id="doctorFilterForm" class="w-100">
    <input type="hidden" name="quick_filter" id="quickFilterInput" value="{{ request('quick_filter') }}">

    <div class="card border-0 p-4 mb-4 shadow-sm" style="background: var(--white); border-radius: 16px;">
        <div class="luxury-filter-row">
            <div class="d-flex flex-wrap gap-2">
                <button type="button" onclick="applyQuickFilter('today')" class="btn btn-sm {{ request('quick_filter') == 'today' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'today' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.today') }}</button>
                <button type="button" onclick="applyQuickFilter('this_week')" class="btn btn-sm {{ request('quick_filter') == 'this_week' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'this_week' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisWeek') }}</button>
                <button type="button" onclick="applyQuickFilter('this_month')" class="btn btn-sm {{ (request('quick_filter') == 'this_month' || !request('quick_filter')) ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ (request('quick_filter') == 'this_month' || !request('quick_filter')) ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisMonth') }}</button>
                <button type="button" onclick="applyQuickFilter('this_year')" class="btn btn-sm {{ request('quick_filter') == 'this_year' ? 'btn-primary' : 'btn-light' }} px-3 py-2" style="border-radius: 8px; {{ request('quick_filter') == 'this_year' ? 'background-color: var(--secondary);' : '' }}">{{ __('messages.thisYear') }}</button>
            </div>

            <div class="luxury-date-inputs-wrapper">
                <div class="luxury-date-field">
                    <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.fromDate') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="document.getElementById('quickFilterInput').value=''; document.getElementById('doctorFilterForm').submit();" class="form-control form-control-luxury">
                </div>
                <div class="luxury-date-field">
                    <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.toDate') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" onchange="document.getElementById('quickFilterInput').value=''; document.getElementById('doctorFilterForm').submit();" class="form-control form-control-luxury">
                </div>
            </div>
        </div>
    </div>
</form>

<div class="doctor-metrics-grid">
    <div class="doctor-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.total_revenue') }}</span>
        <h3 class="doctor-num-total mb-1"><span class="text-nowrap">{{ $currencySymbol }}{{ number_format($total_earned_sum, 2) }}</span></h3>
        <small class="text-muted text-xs d-block">{{ __('messages.collected') }}</small>
    </div>

    <div class="doctor-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.total_appointments') }}</span>
        <h3 class="doctor-num-count mb-1" style="font-size: 1.6rem;">{{ $total_appointments }}</h3>
        <small class="text-muted text-xs d-block">{{ __('messages.attended_slots') }}</small>
    </div>

    <div class="doctor-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.top_doctor') }}</span>
        <h3 class="doctor-num-top mb-1 text-truncate" style="font-size: 1.3rem; max-width: 100%;" title="{{ $top_doctor }}">{{ $top_doctor }}</h3>
        <small class="text-muted text-xs d-block">{{ __('messages.highest_earner') }}</small>
    </div>

    <div class="doctor-premium-card">
        <span class="text-xs fw-bold text-muted mb-2 d-block">{{ __('messages.active_doctors') }}</span>
        <h3 class="text-dark fw-bold mb-1" style="font-weight: 700; font-size: 1.6rem;">{{ $doctor_count }}</h3>
        <small class="text-muted text-xs d-block">{{ __('messages.monitored') }}</small>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-secondary text-xs fw-bold border-0">
                        <th class="py-3">{{ __('messages.doctorColumn') }}</th>
                        <th class="py-3 text-center">{{ __('messages.appointmentsColumn') }}</th>
                        <th class="py-3 text-end">{{ __('messages.totalEarned') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr class="border-bottom">
                            <td class="py-3 fw-medium text-dark">
                                {{ $row->doctor_name ?? __('messages.unassigned') }}
                            </td>
                            <td class="py-3 text-center fw-semibold text-secondary">
                                {{ $row->appointment_count }}
                            </td>
                            <td class="py-3 text-end fw-bold text-nowrap" style="color: var(--secondary);">
                                {{ $currencySymbol }}{{ number_format($row->total_earned, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted text-sm fw-medium">
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
    document.getElementById('doctorFilterForm').submit();
}
</script>

@endsection
