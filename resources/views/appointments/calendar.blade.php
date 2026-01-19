@extends('layouts.dashboard')

@section('title', 'Appointment Calendar')
@section('page-title', 'Appointment Calendar')

@section('styles')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<style>
    .fc-event {
        cursor: pointer;
        padding: 2px 4px;
        font-size: 0.85em;
        border: none;
        transition: all 0.2s;
    }
    .fc-event:hover {
        opacity: 0.9;
        transform: scale(1.02);
    }
    .fc-daygrid-day-number {
        font-weight: 500;
        color: var(--dark);
        text-decoration: none;
    }
    .fc-col-header-cell-cushion {
        color: var(--dark);
        text-decoration: none;
        font-weight: 600;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .fc-button-primary {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
    }
    .fc-button-primary:not(:disabled):active, 
    .fc-button-primary:not(:disabled).fc-button-active {
        background-color: var(--secondary) !important;
        border-color: var(--secondary) !important;
    }
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 700;
        color: var(--dark);
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }
</style>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <!-- Filters -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-4">
                        <select id="doctorFilter" class="form-select">
                            <option value="">All Doctors</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap gap-3 justify-content-md-end mt-3 mt-md-0">
                            <div class="legend-item">
                                <span class="legend-color" style="background: #3b82f6"></span> Scheduled
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background: #10b981"></span> Confirmed
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background: #f59e0b"></span> Waiting
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background: #8b5cf6"></span> In Progress
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background: #6b7280"></span> Completed
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var doctorFilter = document.getElementById('doctorFilter');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'bootstrap5',
            events: {
                url: '{{ route("appointments.events") }}',
                method: 'GET',
                extraParams: function() {
                    return {
                        doctor_id: doctorFilter.value
                    };
                }
            },
            dayMaxEvents: true, // allow "more" link when too many events
            eventClick: function(info) {
                // Determine if event has a URL (it should)
                if (info.event.url) {
                    // Let default behavior happen (navigate to URL)
                    return;
                }
            },
            eventDidMount: function(info) {
                // Add tooltip with status
                info.el.title = info.event.extendedProps.status + ': ' + info.event.title;
            }
        });

        calendar.render();

        // Refetch events when filter changes
        doctorFilter.addEventListener('change', function() {
            calendar.refetchEvents();
        });
    });
</script>
@endsection
