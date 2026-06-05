@extends('layouts.dashboard')

@section('title', __('messages.medicalRecordsDetails'))
@section('page-title', __('messages.medicalRecordsDetails'))

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header   py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">{{ __('messages.medicalRecordsDetails') }} #{{ $medicalRecord->id }}</h5>
                <div class="btn-group">
                    <a href="{{ route('medical-records.print-prescription', $medicalRecord) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-prescription me-1"></i> {{ __('messages.printRx') }}
                    </a>
                    <a href="{{ route('medical-records.print-report', $medicalRecord) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-file-medical me-1"></i> {{ __('messages.printReport') }}
                    </a>
                    <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> {{ __('messages.edit') }}
                    </a>
                    <form action="{{ route('medical-records.destroy', $medicalRecord) }}" method="POST" class="d-inline" onsubmit="return confirm(window.translations[document.documentElement.lang || 'en'].confirmDeleteMedicalRecord)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash me-1"></i> {{ __('messages.delete') }}
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">{{ __('messages.patient') }}</small>
                        <p class="fw-bold mb-0">{{ $medicalRecord->patient->name }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted text-uppercase">{{ __('messages.visitDate') }}</small>
                        <p class="fw-bold mb-0">{{ $medicalRecord->visit_date->format('Y-m-d') }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">{{ __('messages.diagnosis') }}</label>
                    <div class="p-2   rounded">{{ $medicalRecord->diagnosis }}</div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">{{ __('messages.chiefComplaint') }}</label>
                    <p>{{ $medicalRecord->chief_complaint }}</p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">{{ __('messages.treatmentPlan') }}</label>
                    <div class="p-2 border rounded  ">
                        {!! nl2br(e($medicalRecord->treatment_plan ?? 'N/A')) !!}
                    </div>
                </div>

                @if($medicalRecord->notes)
                <div class="mb-3">
                    <label class="text-muted small">{{ __('messages.notes') }}</label>
                    <p>{{ $medicalRecord->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
