<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Models\PatientFile;
use App\Services\PatientService;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Requests\Patient\UploadPatientFileRequest;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Patient::class);
        $patients = $this->patientService->getAllPatients($request->only(['search', 'sort', 'direction']));
        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        $this->authorize('create', Patient::class);
        return view('patients.create');
    }

    public function store(StorePatientRequest $request)
    {
        $this->authorize('create', Patient::class);
        try {
            $patient = $this->patientService->createPatient($request->validated());
            if ($request->wantsJson()) {
                 return response()->json([
                     'message' => 'Patient added successfully',
                     'patient' => $patient,
                     'redirect' => route('patients.show', $patient)
                 ], 201);
            }
            return redirect()->route('patients.show', $patient)
                ->with('success', __('messages.patientAdded'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Patient Creation Failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            if ($request->wantsJson()) {
                 return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error creating patient: ' . $e->getMessage());
        }
    }

    public function show(Patient $patient)
    {
        $this->authorize('view', $patient);
        $patient->load([
            'appointments.doctor.user',
            'appointments.vital',
            'files.uploader',
            'medicalRecords' => fn($q) => $q->orderBy('created_at', 'desc'),
            'medicalRecords.doctor.user',
            'medicalRecords.prescription.items',
            'medicalRecords.appointment.vital',
        ]);
        $appointmentStats = [
            'total' => $patient->appointments->count(),
            'completed' => $patient->appointments->where('status', 'completed')->count(),
            'upcoming' => $patient->appointments->where('date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])->count(),
        ];

        if ($patient->known_allergies) {
            session()->flash('warning', __('messages.patientAllergyWarning', ['allergies' => $patient->known_allergies]));
        }

        if ($patient->insurance_expiry_date && \Carbon\Carbon::parse($patient->insurance_expiry_date)->diffInDays(now()) <= 30) {
            session()->flash('warning', __('messages.insuranceExpiryWarning', ['date' => $patient->insurance_expiry_date]));
        }

        return view('patients.show', compact('patient', 'appointmentStats'));
    }

    public function edit(Patient $patient)
    {
        $this->authorize('update', $patient);
        return view('patients.edit', compact('patient'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $this->authorize('update', $patient);
        \Illuminate\Support\Facades\Log::info('Updating Patient ID: ' . $patient->id, $request->validated());
        $this->patientService->updatePatient($patient, $request->validated());
        return redirect()->route('patients.index')
            ->with('success', __('messages.patientUpdated'));
    }

    public function destroy(Patient $patient)
    {
        $this->authorize('delete', $patient);
        try {
            $this->patientService->deletePatient($patient);
            return back()->with('success', __('messages.patientDeleted'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.systemError'));
        }
    }

    public function uploadFile(UploadPatientFileRequest $request, Patient $patient)
    {
        $this->authorize('update', $patient);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('patient-files/' . $patient->id, $filename, 'public');

        $patient->files()->create([
            'uploaded_by' => auth()->id(),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'category' => $request->category,
            'description' => $request->description,
        ]);

        return back()->with('success', __('messages.fileUploaded'));
    }

    public function downloadFile(Patient $patient, PatientFile $file)
    {
        $this->authorize('view', $patient);
        if ($file->patient_id !== $patient->id) {
            abort(404);
        }
        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    public function deleteFile(Patient $patient, PatientFile $file)
    {
        $this->authorize('deleteFile', Patient::class);
        if ($file->patient_id !== $patient->id) {
            abort(404);
        }

        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();
        return back()->with('success', __('messages.fileDeleted'));
    }
}
