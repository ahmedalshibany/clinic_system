@extends('layouts.dashboard')

@section('title', 'New Medical Record')
@section('page-title', 'New Medical Record')

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('medical-records.store') }}" method="POST" id="medicalRecordForm">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
            <input type="hidden" name="doctor_id" value="{{ auth()->user()->doctor->id }}">
            @if($appointment)
                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
            @endif

            @include('medical_records.form')
            
            <div class="card mt-4 mb-5 border-0 shadow-sm">
                <div class="card-body p-4 text-end">
                    <a href="{{ url()->previous() }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Save Record
                    </button>
                    <!-- <button type="submit" name="print" value="1" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-print me-2"></i> Save & Print
                    </button> -->
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
