<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Services\MedicalRecordService;
use App\Http\Requests\MedicalRecord\StoreMedicalRecordRequest;
use App\Http\Requests\MedicalRecord\UpdateMedicalRecordRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class MedicalRecordController extends Controller
{
    protected MedicalRecordService $medicalRecordService;

    public function __construct(MedicalRecordService $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
    }
    /**
     * Display a listing of medical records.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', MedicalRecord::class);
        $data = $this->medicalRecordService->getAllMedicalRecords($request->all());
        return view('medical_records.index', $data);
    }

    public function create(Patient $patient, Request $request)
    {
        $this->authorize('create', MedicalRecord::class);
        $appointment = null;
        if ($request->filled('appointment_id')) {
            $appointment = Appointment::find($request->appointment_id);
        }
        $doctor = Auth::user()->doctor;
        $doctors = collect();
        if (!$doctor) {
            $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        }
        return view('medical_records.create', compact('patient', 'appointment', 'doctor', 'doctors'));
    }

    public function store(StoreMedicalRecordRequest $request)
    {
        $this->authorize('create', MedicalRecord::class);
        try {
            $this->medicalRecordService->createRecordWithPrescription($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating medical record: ' . $e->getMessage());
        }
        return redirect()->route('patients.show', $request->patient_id)
            ->with('success', 'Medical record created successfully.');
    }

    public function show(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);
        $medicalRecord->load(['patient', 'doctor', 'prescription.items']);
        return view('medical_records.show', compact('medicalRecord'));
    }

    public function edit(MedicalRecord $medicalRecord)
    {
        $this->authorize('update', $medicalRecord);
        $medicalRecord->load(['prescription.items']);
        return view('medical_records.edit', compact('medicalRecord'));
    }

    public function update(UpdateMedicalRecordRequest $request, MedicalRecord $medicalRecord)
    {
        $this->authorize('update', $medicalRecord);
        try {
            $this->medicalRecordService->updateRecordWithPrescription($medicalRecord, $request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating medical record: ' . $e->getMessage());
        }
        return redirect()->route('medical-records.show', $medicalRecord)
            ->with('success', 'Medical record updated successfully.');
    }

    public function destroy(MedicalRecord $medicalRecord)
    {
        $this->authorize('delete', $medicalRecord);
        try {
            $this->medicalRecordService->deleteRecord($medicalRecord);
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting medical record: ' . $e->getMessage());
        }
        return redirect()->route('medical-records.index')
            ->with('success', 'Medical record deleted successfully.');
    }

    public function printPrescription(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);
        $medicalRecord->load(['patient', 'doctor', 'prescription.items']);
        if (!$medicalRecord->prescription) {
            return back()->with('error', 'No prescription found for this record.');
        }
        return view('medical_records.print_prescription', compact('medicalRecord'));
    }

    public function printReport(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);
        $medicalRecord->load(['patient', 'doctor', 'prescription.items']);
        return view('medical_records.print_report', compact('medicalRecord'));
    }
}
