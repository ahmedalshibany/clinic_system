@extends('layouts.dashboard')

@section('title', 'Edit Medical Record')
@section('page-title', 'Edit Medical Record')

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('medical-records.update', $medicalRecord) }}" method="POST" id="medicalRecordForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="patient_id" value="{{ $medicalRecord->patient_id }}">
            <input type="hidden" name="doctor_id" value="{{ $medicalRecord->doctor_id }}">

            @include('medical_records.form', ['record' => $medicalRecord])

            <div class="card mt-4 mb-5 border-0 shadow-sm">
                <div class="card-body p-4 text-end">
                    <a href="{{ route('medical-records.show', $medicalRecord) }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Update Record
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
