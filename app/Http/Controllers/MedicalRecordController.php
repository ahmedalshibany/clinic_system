<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalRecordController extends Controller
{
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

        DB::transaction(function () use ($validated, $request) {
            // Create Medical Record
            $record = MedicalRecord::create($validated);

            // Handle Prescription
            if ($request->filled('prescription_items')) {
                $prescription = Prescription::create([
                    'medical_record_id' => $record->id,
                ]);

                foreach ($request->prescription_items as $item) {
                    $prescription->items()->create($item);
                }
            }

            // If linked to appointment, update status to completed if not already
            if ($record->appointment_id) {
                $appointment = Appointment::find($record->appointment_id);
                if ($appointment && $appointment->status !== 'completed') {
                    $appointment->update(['status' => 'completed', 'completed_at' => now()]);
                }
            }
        });

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

        DB::transaction(function () use ($validated, $request, $medicalRecord) {
            $medicalRecord->update($validated);

            // Handle Prescription Updates (Full Re-sync for simplicity)
            if ($request->filled('prescription_items')) {
                // Determine if prescription exists, if not create one
                $prescription = $medicalRecord->prescription ?? Prescription::create(['medical_record_id' => $medicalRecord->id]);
                
                // Remove old items
                $prescription->items()->delete();

                // Add new items
                foreach ($request->prescription_items as $item) {
                    $prescription->items()->create($item);
                }
            } elseif ($medicalRecord->prescription) {
                // If prescription_items is empty but record has prescription, clear items? 
                // Dependent on business logic. Usually better to keep empty prescription or delete it.
                // Here we will just calculate if we should delete the items.
                // If the user sends an empty array, it means they want to clear it.
                 if ($request->has('prescription_items')) {
                    $medicalRecord->prescription->items()->delete();
                 }
            }
        });

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
