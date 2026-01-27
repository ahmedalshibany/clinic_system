@extends('layouts.dashboard')

@section('title', __('Medical Record Details'))

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">{{ __('Medical Record') }} #{{ $medicalRecord->id }}</h5>
                <div class="btn-group">
                    <a href="{{ route('medical-records.print-prescription', $medicalRecord) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-prescription me-1"></i> {{ __('Print Rx') }}
                    </a>
                    <a href="{{ route('medical-records.print-report', $medicalRecord) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-file-medical me-1"></i> {{ __('Print Report') }}
                    </a>
                    <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> {{ __('Edit') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">{{ __('Patient') }}</small>
                        <p class="fw-bold mb-0">{{ $medicalRecord->patient->name }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted text-uppercase">{{ __('Visit Date') }}</small>
                        <p class="fw-bold mb-0">{{ $medicalRecord->visit_date->format('Y-m-d') }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">{{ __('Diagnosis') }}</label>
                    <div class="p-2 bg-light rounded">{{ $medicalRecord->diagnosis }}</div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">{{ __('Chief Complaint') }}</label>
                    <p>{{ $medicalRecord->chief_complaint }}</p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">{{ __('Treatment Plan') }}</label>
                    <div class="p-2 border rounded bg-light">
                        {!! nl2br(e($medicalRecord->treatment_plan ?? 'N/A')) !!}
                    </div>
                </div>

                @if($medicalRecord->notes)
                <div class="mb-3">
                    <label class="text-muted small">{{ __('Notes') }}</label>
                    <p>{{ $medicalRecord->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
