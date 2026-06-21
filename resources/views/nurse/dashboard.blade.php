@extends('layouts.dashboard')

@section('title', __('messages.dashboard'))
@section('page-title', __('messages.dashboard'))

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
@endsection

@section('content')
<div class="welcome-banner">
    <div class="welcome-content">
        <div class="welcome-text">
            <span class="welcome-badge" id="greeting-badge">
                <i class="fas fa-sun" id="greeting-icon"></i>
                <span id="dashboard-greeting" data-i18n="goodMorning">{{ __('Good Morning') }}</span>
            </span>
            <h1 style="word-break: keep-all; text-wrap: balance; line-height: 1.4;"><span class="d-inline-block" style="white-space: nowrap;"><span data-i18n="welcomeBack">{{ __('Welcome Back,') }}</span> <span class="gradient-text">{{ __('messages.role_nurse') }}</span></span></h1>
            <p data-i18n="dashboardSubtitle">{{ __("Here's what's happening with your clinic today") }}</p>
        </div>
        <div class="welcome-illustration">
            <div class="floating-card card-1"><i class="fas fa-heartbeat"></i></div>
            <div class="floating-card card-2"><i class="fas fa-stethoscope"></i></div>
            <div class="floating-card card-3"><i class="fas fa-pills"></i></div>
        </div>
    </div>
    <div class="welcome-wave"></div>
</div>

<div class="container-fluid px-0 mt-4">
    <div class="row g-4">
        <div class="col-12">
            <div style="background: var(--card-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius); overflow: hidden;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-md) var(--space-lg); background: var(--panel-bg); border-bottom: 1px solid var(--border-hairline);">
                    <h5 style="margin: 0; font-size: var(--text-base); font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: var(--space-sm);">
                        <i class="fas fa-user-nurse" style="color: var(--secondary);"></i>
                        <span data-i18n="triageQueue">{{ __('Triage Queue (To Vitals)') }}</span>
                    </h5>
                    <span style="background: var(--secondary); color: #fff; padding: 2px 10px; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: 600;">{{ $triageQueue->count() }}</span>
                </div>
                <div>
                    <table style="width: 100%; border-collapse: collapse; font-size: var(--text-sm);">
                        <thead>
                            <tr style="background: var(--panel-bg); border-bottom: 1px solid var(--border-hairline);">
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Time') }}</th>
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Patient Name') }}</th>
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Doctor') }}</th>
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Status') }}</th>
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($triageQueue as $appt)
                            <tr style="border-bottom: 1px solid var(--border-hairline);">
                                <td style="padding: var(--space-sm) var(--space-lg);">{{ $appt->time->format('H:i') }}</td>
                                <td style="padding: var(--space-sm) var(--space-lg); font-weight: 600; color: var(--text-primary);">{{ $appt->patient->name }}</td>
                                <td style="padding: var(--space-sm) var(--space-lg); color: var(--text-secondary);">{{ $appt->doctor->name }}</td>
                                <td style="padding: var(--space-sm) var(--space-lg);">
                                    @if($appt->status === 'checked_in')
                                    <span style="color: var(--secondary); font-weight: 500;">{{ __('messages.checked_in') }}</span>
                                    @else
                                    <span style="color: var(--info); font-weight: 500;">{{ __('Confirmed') }}</span>
                                    @endif
                                </td>
                                <td style="padding: var(--space-sm) var(--space-lg);">
                                    <a href="{{ route('nurse.vitals.create', $appt->id) }}" style="display: inline-flex; align-items: center; gap: var(--space-xs); padding: 4px 12px; background: var(--secondary); color: #fff; border-radius: var(--radius-sm); font-size: var(--text-xs); font-weight: 600; text-decoration: none; transition: opacity var(--duration-fast) var(--ease-out);" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                        <i class="fas fa-heartbeat"></i> {{ __('Record Vitals') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding: var(--space-xl) var(--space-lg); text-align: center; color: var(--text-muted);">{{ __('Thinking... No patients in triage queue.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div style="background: var(--card-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius); overflow: hidden;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-md) var(--space-lg); background: var(--panel-bg); border-bottom: 1px solid var(--border-hairline);">
                    <h5 style="margin: 0; font-size: var(--text-base); font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: var(--space-sm);">
                        <i class="fas fa-chair" style="color: var(--warning);"></i>
                        <span data-i18n="waitingRoomReady">{{ __('Waiting Room (Ready for Doctor)') }}</span>
                    </h5>
                    <span style="background: var(--warning); color: #fff; padding: 2px 10px; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: 600;">{{ $waitingList->count() }}</span>
                </div>
                <div>
                    <table style="width: 100%; border-collapse: collapse; font-size: var(--text-sm);">
                        <thead>
                            <tr style="background: var(--panel-bg); border-bottom: 1px solid var(--border-hairline);">
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Time') }}</th>
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Patient Name') }}</th>
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Doctor') }}</th>
                                <th style="padding: var(--space-sm) var(--space-lg); text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waitingList as $appt)
                            <tr style="border-bottom: 1px solid var(--border-hairline);">
                                <td style="padding: var(--space-sm) var(--space-lg);">{{ $appt->time->format('H:i') }}</td>
                                <td style="padding: var(--space-sm) var(--space-lg); font-weight: 600; color: var(--text-primary);">{{ $appt->patient->name }}</td>
                                <td style="padding: var(--space-sm) var(--space-lg); color: var(--text-secondary);">{{ $appt->doctor->name }}</td>
                                <td style="padding: var(--space-sm) var(--space-lg);"><span style="color: var(--warning); font-weight: 500;">{{ __('Waiting') }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding: var(--space-xl) var(--space-lg); text-align: center; color: var(--text-muted);">{{ __('No patients waiting.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
