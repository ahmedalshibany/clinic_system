@extends('layouts.dashboard')

@section('title', __('messages.editAppt'))
@section('page-title', __('messages.editAppt'))
@section('page-i18n', 'editAppt')

@section('styles')
<style>
.slot-grid {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-sm, 6px);
    margin-top: var(--space-md, 10px);
}
.slot-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 80px;
    padding: var(--space-md, 10px) var(--space-lg, 16px);
    border-radius: var(--radius, 10px);
    border: 1px solid var(--clay, #c4b8a5);
    background: var(--white, #f5f0e8);
    color: var(--text-primary, #2c2c2c);
    font-size: var(--text-sm, 0.875rem);
    font-weight: 600;
    cursor: pointer;
    transition: all 150ms cubic-bezier(0.16, 1, 0.3, 1);
}
.slot-btn:hover {
    border-color: var(--secondary, #0f3d3e);
    background: color-mix(in srgb, var(--secondary, #0f3d3e) 6%, transparent);
    transform: translateY(-1px);
}
.slot-btn:focus-visible {
    outline: 2px solid var(--secondary, #0f3d3e);
    outline-offset: 2px;
}
.slot-btn.active {
    border-color: var(--secondary, #0f3d3e);
    background: var(--secondary, #0f3d3e);
    color: #fff;
}
.slot-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}
.slot-placeholder {
    color: var(--text-secondary, #555);
    font-size: var(--text-sm, 0.875rem);
    padding: var(--space-lg, 16px) 0;
}
.slot-loading {
    color: var(--text-secondary, #555);
    font-size: var(--text-sm, 0.875rem);
    padding: var(--space-lg, 16px) 0;
}
.slot-loading i {
    margin-right: 6px;
}
[dir="rtl"] .slot-loading i {
    margin-right: 0;
    margin-left: 6px;
}
[dir="rtl"] .slot-btn {
    font-family: var(--font-ar, 'Tajawal', sans-serif);
}
</style>
@endsection

@section('content')
<div class="row fade-in">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6>{{ __('messages.editAppt') }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('appointments.update', $appointment) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="patient_id" class="form-label">{{ __('messages.patient') }}</label>
                            <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">{{ __('messages.selectPatient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }} (ID: {{ $patient->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="doctor_id" class="form-label">{{ __('messages.doctor') }}</label>
                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                <option value="">{{ __('messages.selectDoctor') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }} - {{ $doctor->department }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-4 mt-2">
                        <div class="col-md-6">
                            <label for="date" class="form-label">{{ __('messages.date') }}</label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $appointment->date->format('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.time') }}</label>
                            <input type="hidden" name="time" id="time" value="{{ old('time', $appointment->time) }}">
                            <div id="slot-container" class="slot-grid">
                                <div class="slot-placeholder" id="slot-placeholder">{{ __('messages.selectSlot') }}</div>
                            </div>
                            @error('time')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-4 mt-2">
                        <div class="col-md-6">
                            <label for="status" class="form-label">{{ __('messages.status') }}</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>{{ __('messages.confirmed') }}</option>
                                <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                                <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label">{{ __('messages.type') }}</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="Checkup" {{ old('type', $appointment->type) == 'Checkup' ? 'selected' : '' }}>{{ __('messages.typeCheckup') }}</option>
                                <option value="Consultation" {{ old('type', $appointment->type) == 'Consultation' ? 'selected' : '' }}>{{ __('messages.typeConsultation') }}</option>
                                <option value="Follow-up" {{ old('type', $appointment->type) == 'Follow-up' ? 'selected' : '' }}>{{ __('messages.typeFollowUp') }}</option>
                                <option value="Emergency" {{ old('type', $appointment->type) == 'Emergency' ? 'selected' : '' }}>{{ __('messages.typeEmergency') }}</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="{{ url()->previous() && url()->previous() !== url()->current() ? url()->previous() : route('appointments.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4">{{ __('messages.updateAppt') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('date');
    const slotContainer = document.getElementById('slot-container');
    const timeInput = document.getElementById('time');
    const lang = document.documentElement.lang || 'en';
    const isAr = lang === 'ar';

    function getMeta(name) {
        const t = {
            'selectSlot': '{{ __("messages.selectSlot") }}',
            'loadingSlots': '{{ __("messages.loadingSlots") }}',
            'noSlotsAvailable': '{{ __("messages.noSlotsAvailable") }}',
            'slotSelected': '{{ __("messages.slotSelected") }}',
        };
        return t[name] || name;
    }

    function fetchSlots() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;

        if (!doctorId || !date) {
            slotContainer.innerHTML = '<div class="slot-placeholder">' + getMeta('selectSlot') + '</div>';
            return;
        }

        slotContainer.innerHTML = '<div class="slot-loading"><i class="fas fa-spinner fa-spin"></i> ' + getMeta('loadingSlots') + '</div>';

        fetch('/doctors/' + doctorId + '/available-slots/' + date)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var slots = data.slots || [];
                if (slots.length === 0) {
                    slotContainer.innerHTML = '<div class="slot-placeholder">' + getMeta('noSlotsAvailable') + '</div>';
                    return;
                }
                var html = '';
                slots.forEach(function (s) {
                    var active = s === timeInput.value ? ' active' : '';
                    html += '<button type="button" class="slot-btn' + active + '" data-time="' + s + '">' + s + '</button>';
                });
                slotContainer.innerHTML = html;

                slotContainer.querySelectorAll('.slot-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        slotContainer.querySelectorAll('.slot-btn').forEach(function (b) { b.classList.remove('active'); });
                        btn.classList.add('active');
                        timeInput.value = btn.dataset.time;
                    });
                });
            })
            .catch(function () {
                slotContainer.innerHTML = '<div class="slot-placeholder">' + getMeta('noSlotsAvailable') + '</div>';
            });
    }

    doctorSelect.addEventListener('change', fetchSlots);
    dateInput.addEventListener('change', fetchSlots);

    // If both already selected, load slots
    if (doctorSelect.value && dateInput.value) {
        fetchSlots();
    }
});
</script>
@endsection
