<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use App\Services\MedicalRecordService;

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
        $query = MedicalRecord::with(['patient', 'doctor']);

        // Filter by Patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by Doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->date);
        }

        // Search in diagnosis
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('diagnosis', 'like', "%{$search}%")
                  ->orWhere('diagnosis_code', 'like', "%{$search}%");
        }

        $records = $query->latest('visit_date')->paginate(10)->withQueryString();
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();

        return view('medical_records.index', compact('records', 'patients', 'doctors'));
    }

    /**
     * Show the form for creating a new medical record.
     */
    public function create(Patient $patient, Request $request)
    {
        $appointment = null;
        if ($request->filled('appointment_id')) {
            $appointment = Appointment::find($request->appointment_id);
        }

        return view('medical_records.create', compact('patient', 'appointment'));
    }

    /**
     * Store a newly created medical record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'required|date',
            'diagnosis' => 'required|string',
            'diagnosis_code' => 'nullable|string',
            'vital_signs' => 'required|array',
            'vital_signs.bp_systolic' => 'nullable|numeric',
            'vital_signs.bp_diastolic' => 'nullable|numeric',
            'vital_signs.temp' => 'nullable|numeric',
            'vital_signs.pulse' => 'nullable|numeric',
            'vital_signs.weight' => 'nullable|numeric',
            'chief_complaint' => 'required|string',
            'history_of_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:visit_date',
            // Prescription validation
            'prescription_items' => 'nullable|array',
            'prescription_items.*.medication_name' => 'required_with:prescription_items|string',
            'prescription_items.*.dosage' => 'required_with:prescription_items|string',
            'prescription_items.*.frequency' => 'required_with:prescription_items|string',
            'prescription_items.*.duration' => 'required_with:prescription_items|string',
        ]);

        try {
            $this->medicalRecordService->createRecordWithPrescription($validated);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating medical record: ' . $e->getMessage());
        }

        return redirect()->route('patients.show', $validated['patient_id'])
            ->with('success', 'Medical record created successfully.');
    }

    /**
     * Display the specified medical record.
     */
    public function show(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['patient', 'doctor', 'prescription.items']);
        return view('medical_records.show', compact('medicalRecord'));
    }

    /**
     * Show the form for editing the specified medical record.
     */
    public function edit(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['prescription.items']);
        return view('medical_records.edit', compact('medicalRecord'));
    }

    /**
     * Update the specified medical record.
     */
    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'diagnosis' => 'required|string',
            'diagnosis_code' => 'nullable|string',
            'vital_signs' => 'required|array',
            'vital_signs.bp_systolic' => 'nullable|numeric',
            'vital_signs.bp_diastolic' => 'nullable|numeric',
            'vital_signs.temp' => 'nullable|numeric',
            'vital_signs.pulse' => 'nullable|numeric',
            'vital_signs.weight' => 'nullable|numeric',
            'chief_complaint' => 'required|string',
            'history_of_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            // Prescription validation
            'prescription_items' => 'nullable|array',
            'prescription_items.*.medication_name' => 'required_with:prescription_items|string',
            'prescription_items.*.dosage' => 'required_with:prescription_items|string',
            'prescription_items.*.frequency' => 'required_with:prescription_items|string',
            'prescription_items.*.duration' => 'required_with:prescription_items|string',
        ]);

        try {
            $this->medicalRecordService->updateRecordWithPrescription($medicalRecord, $validated);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating medical record: ' . $e->getMessage());
        }

        return redirect()->route('medical-records.show', $medicalRecord)
            ->with('success', 'Medical record updated successfully.');
    }

    /**
     * Print Prescription.
     */
    public function printPrescription(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['patient', 'doctor', 'prescription.items']);
        
        if (!$medicalRecord->prescription) {
            return back()->with('error', 'No prescription found for this record.');
        }

        return view('medical_records.print_prescription', compact('medicalRecord'));
    }

    /**
     * Print Medical Report.
     */
    public function printReport(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['patient', 'doctor', 'prescription.items']);
        return view('medical_records.print_report', compact('medicalRecord'));
    }
}
