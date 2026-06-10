@extends('layouts.dashboard')

@section('page-title', __('messages.appointments_report'))
@section('page-i18n', 'appointments_report')

@section('content')
<style>
    /* ── Action Bar ── */
    .davinci-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        margin-bottom: var(--space-xl);
    }

    /* ── Filter Card ── */
    .davinci-filter-card {
        background: var(--white);
        border: 1px solid var(--border-hairline);
        border-radius: var(--radius-lg);
        padding: var(--space-xl);
        margin-bottom: var(--space-xl);
        box-shadow: var(--shadow-soft);
    }

    /* ── Pill Toggle ── */
    .pills-deck {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-sm);
    }
    .pill-davinci {
        border-radius: var(--radius);
        padding: 0.55rem 1.25rem;
        font-size: var(--text-sm);
        font-weight: 600;
        border: 1px solid transparent;
        transition: background-color var(--duration-fast) var(--ease-out),
                    color var(--duration-fast) var(--ease-out),
                    border-color var(--duration-fast) var(--ease-out);
    }
    .pill-davinci.active {
        background-color: var(--secondary);
        color: var(--white);
    }
    .pill-davinci.inactive {
        background-color: rgba(15, 61, 62, 0.03);
        color: var(--text-secondary);
        border-color: var(--border-hairline);
    }
    .pill-davinci.inactive:hover {
        background-color: rgba(15, 61, 62, 0.06);
    }

    /* ── Sfumato Metrics Grid ── */
    .davinci-metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--space-lg);
        margin-bottom: var(--space-xl);
    }
    .sfumato-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: var(--space-xl);
        border: 1px solid var(--border-hairline);
        box-shadow: var(--shadow-soft);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform var(--duration-base) var(--ease-out),
                    box-shadow var(--duration-base) var(--ease-out);
    }
    .sfumato-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .sfumato-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 0%, var(--card-accent), transparent 65%);
        opacity: 0.05;
        pointer-events: none;
    }
    .sfumato-card:hover::before {
        opacity: 0.09;
    }

    .davinci-card-title {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: var(--space-sm);
        display: block;
        position: relative;
        z-index: 1;
    }
    .davinci-card-value {
        font-size: var(--text-3xl);
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: var(--space-sm);
        position: relative;
        z-index: 1;
    }
    .davinci-card-sub {
        font-size: var(--text-xs);
        color: var(--text-secondary);
        font-weight: 500;
        position: relative;
        z-index: 1;
    }

    @media (max-width: 992px) {
        .davinci-metrics-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .davinci-metrics-grid { grid-template-columns: 1fr; }
    }

    /* ── Table ── */
    .davinci-table-wrapper {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        border: 1px solid var(--border-hairline);
        overflow: hidden;
    }
    .davinci-table th {
        color: var(--text-primary);
        font-weight: 700;
        font-size: var(--text-xs);
        letter-spacing: 0.05em;
        padding: 1rem 1.5rem;
        background: var(--cream);
        border-bottom: 1px solid var(--border-hairline);
    }
    .davinci-table td {
        padding: 1.15rem 1.5rem;
        font-size: var(--text-sm);
        border-bottom: 1px solid var(--border-hairline);
        vertical-align: middle;
    }
    .davinci-table tbody tr:last-child td {
        border-bottom: none;
    }
    .davinci-table tbody tr:hover td {
        background: rgba(15, 61, 62, 0.015);
    }

    /* ── Badges ── */
    .davinci-badge {
        padding: 0.4rem 0.95rem;
        font-size: var(--text-xs);
        font-weight: 600;
        border-radius: var(--radius-full);
        display: inline-block;
    }

    /* ── Gradient Divider ── */
    .davinci-gradient-divider {
        height: 1px;
        background: linear-gradient(to right, transparent, var(--border-light), transparent);
    }

    /* ── RTL / Arabic Overrides ── */
    [dir="rtl"] .back-arrow-icon {
        transform: scaleX(-1);
    }
    [dir="rtl"] .davinci-gradient-divider {
        background: linear-gradient(to left, transparent, var(--border-light), transparent);
    }
    [dir="rtl"] .davinci-table th {
        letter-spacing: 0;
    }
</style>

<div class="davinci-action-bar">
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i class="fas fa-arrow-left back-arrow-icon"></i>
        <span>{{ __('messages.backToReports') }}</span>
    </a>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="fas fa-print"></i>{{ __('messages.print') }}</button>
        <button class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="fas fa-file-excel"></i>{{ __('messages.exportExcel') }}</button>
    </div>
</div>

<form action="{{ route('reports.appointments') }}" method="GET" id="appointmentsFilterForm" class="w-100">
    <input type="hidden" name="quick_filter" id="quickFilterInput" value="{{ request('quick_filter') }}">

    <div class="davinci-filter-card">
        <div class="row align-items-center gy-3">
            <div class="col-xl-5 col-lg-12">
                <div class="pills-deck">
                    <button type="button" onclick="applyQuickFilter('today')" class="pill-davinci {{ request('quick_filter') == 'today' ? 'active' : 'inactive' }}">{{ __('messages.today') }}</button>
                    <button type="button" onclick="applyQuickFilter('this_week')" class="pill-davinci {{ request('quick_filter') == 'this_week' ? 'active' : 'inactive' }}">{{ __('messages.thisWeek') }}</button>
                    <button type="button" onclick="applyQuickFilter('this_month')" class="pill-davinci {{ (!request('quick_filter') || request('quick_filter') == 'this_month') ? 'active' : 'inactive' }}">{{ __('messages.thisMonth') }}</button>
                    <button type="button" onclick="applyQuickFilter('this_year')" class="pill-davinci {{ request('quick_filter') == 'this_year' ? 'active' : 'inactive' }}">{{ __('messages.thisYear') }}</button>
                </div>
            </div>
            <div class="col-xl-7 col-lg-12">
                <div class="d-flex flex-wrap gap-3 justify-content-xl-end justify-content-start">
                    <div class="flex-grow-1 flex-md-grow-0" style="min-width: 140px;">
                        <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.fromDate') }}</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="clearQuickFilter()" class="form-control w-100">
                    </div>
                    <div class="flex-grow-1 flex-md-grow-0" style="min-width: 140px;">
                        <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.toDate') }}</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" onchange="clearQuickFilter()" class="form-control w-100">
                    </div>
                    <div class="flex-grow-1 flex-md-grow-0" style="min-width: 160px;">
                        <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.doctor') }}</label>
                        <select name="doctor_id" onchange="clearQuickFilter()" class="form-control form-select w-100">
                            <option value="">{{ __('messages.allDoctors') }}</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ $doctorId == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->user->name ?? ($doc->display_name ?? '—') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-grow-1 flex-md-grow-0" style="min-width: 140px;">
                        <label class="form-label text-xs fw-bold text-muted mb-1">{{ __('messages.status') }}</label>
                        <select name="status" onchange="clearQuickFilter()" class="form-control form-select w-100">
                            <option value="">{{ __('messages.allStatuses') }}</option>
                            <option value="scheduled" {{ $status == 'scheduled' ? 'selected' : '' }}>{{ __('messages.status_scheduled') }}</option>
                            <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>{{ __('messages.status_completed') }}</option>
                            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>{{ __('messages.status_cancelled') }}</option>
                            <option value="no-show" {{ ($status == 'no-show' || $status == 'no_show') ? 'selected' : '' }}>{{ __('messages.status_no_show') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="davinci-metrics-grid">
    <div class="sfumato-card" style="--card-accent: #0f3d3e;">
        <span class="davinci-card-title">{{ __('messages.total_appointments') }}</span>
        <div class="davinci-card-value" style="color: var(--secondary);">{{ number_format($total_appointments_count) }}</div>
        <span class="davinci-card-sub">{{ __('messages.gross_slots') }}</span>
    </div>
    <div class="sfumato-card" style="--card-accent: #2e7d32;">
        <span class="davinci-card-title">{{ __('messages.completed_appointments') }}</span>
        <div class="davinci-card-value" style="color: var(--success);">{{ number_format($completed_count) }}</div>
        <span class="davinci-card-sub">
            @if($total_appointments_count > 0)
                {{ round(($completed_count / $total_appointments_count) * 100, 1) }}% {{ __('messages.success_rate') }}
            @else 0% @endif
        </span>
    </div>
    <div class="sfumato-card" style="--card-accent: #2b5c8f;">
        <span class="davinci-card-title">{{ __('messages.scheduled_appointments') }}</span>
        <div class="davinci-card-value" style="color: var(--info);">{{ number_format($scheduled_count) }}</div>
        <span class="davinci-card-sub">{{ __('messages.upcoming_pipeline') }}</span>
    </div>
    <div class="sfumato-card" style="--card-accent: #a94442;">
        <span class="davinci-card-title">{{ __('messages.voided_appointments') }}</span>
        <div class="davinci-card-value" style="color: #6e2525;">{{ number_format($cancelled_and_noshow) }}</div>
        <span class="davinci-card-sub">{{ __('messages.attrition_volume') }}</span>
    </div>
</div>

<div class="davinci-table-wrapper card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table davinci-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('messages.dateTimeColumn') }}</th>
                        <th>{{ __('messages.patientColumn') }}</th>
                        <th>{{ __('messages.doctorColumn') }}</th>
                        <th class="text-center" style="width: 160px;">{{ __('messages.statusColumn') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                        <tr>
                            <td class="fw-medium text-secondary">
                                {{ $appt->date ? \Carbon\Carbon::parse($appt->date)->format('Y-m-d H:i') : '—' }}
                            </td>
                            <td class="fw-bold" style="color: var(--dark);">
                                {{ $appt->patient->name ?? __('messages.unassigned') }}
                            </td>
                            <td class="text-secondary fw-medium">
                                {{ $appt->doctor->user->name ?? ($appt->doctor->display_name ?? '—') }}
                            </td>
                            <td class="text-center">
                                @if(in_array($appt->status, ['completed', 'تمت']))
                                    <span class="davinci-badge" style="background: rgba(46, 125, 50, 0.1); color: var(--success);">{{ __('messages.status_completed') }}</span>
                                @elseif(in_array($appt->status, ['scheduled', 'confirmed', 'مجدول']))
                                    <span class="davinci-badge" style="background: rgba(61, 90, 128, 0.1); color: var(--info);">{{ __('messages.status_scheduled') }}</span>
                                @elseif(in_array($appt->status, ['cancelled', 'ملغي']))
                                    <span class="davinci-badge" style="background: rgba(139, 58, 58, 0.1); color: var(--danger);">{{ __('messages.status_cancelled') }}</span>
                                @else
                                    <span class="davinci-badge" style="background: rgba(191, 140, 48, 0.12); color: var(--warning);">{{ __('messages.status_no_show') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="text-center py-5 text-secondary fw-medium">
                                    <div class="mb-2"><i class="fas fa-calendar-times" style="font-size: 1.5rem; opacity: 0.25;"></i></div>
                                    {{ __('messages.no_data_available') }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($appointments->hasPages())
            <div class="davinci-gradient-divider"></div>
            <div class="pagination-controls">
                <div class="pagination-info">
                    <span>{{ __('messages.showing') }}</span> <strong>{{ $appointments->firstItem() }}-{{ $appointments->lastItem() }}</strong> <span>{{ __('messages.of') }}</span> <strong>{{ $appointments->total() }}</strong>
                </div>
                <div class="pagination-buttons">
                    @if($appointments->onFirstPage())
                        <button disabled><i class="fas fa-chevron-left pagination-arrow"></i> <span>{{ __('messages.previous') }}</span></button>
                    @else
                        <a href="{{ $appointments->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left pagination-arrow"></i> <span>{{ __('messages.previous') }}</span></a>
                    @endif
                    @if($appointments->hasMorePages())
                        <a href="{{ $appointments->nextPageUrl() }}" class="btn btn-light btn-sm"><span>{{ __('messages.next') }}</span> <i class="fas fa-chevron-right pagination-arrow"></i></a>
                    @else
                        <button disabled><span>{{ __('messages.next') }}</span> <i class="fas fa-chevron-right pagination-arrow"></i></button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function applyQuickFilter(filterValue) {
    document.getElementById('quickFilterInput').value = filterValue;
    var from = document.getElementsByName('date_from')[0];
    var to = document.getElementsByName('date_to')[0];
    if (from) from.value = '';
    if (to) to.value = '';
    document.getElementById('appointmentsFilterForm').submit();
}
function clearQuickFilter() {
    document.getElementById('quickFilterInput').value = '';
    document.getElementById('appointmentsFilterForm').submit();
}
</script>
@endsection
