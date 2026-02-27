<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Models\PatientFile;
use App\Services\PatientService;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $patients = $query->paginate(10)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient.
     */
    public function store(StorePatientRequest $request)
    {
        try {
            // Validation
            $validated = $request->validated();

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('patients', 'public');
                $validated['photo'] = $path;
            }

            $patient = $this->patientService->createPatient($validated);

            if ($request->wantsJson()) {
                 return response()->json([
                     'message' => 'Patient added successfully',
                     'patient' => $patient,
                     'redirect' => route('patients.show', $patient)
                 ], 201);
            }

            return redirect()->route('patients.show', $patient)
                ->with('success', __('Patient added successfully!'));

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

    /**
     * Display the specified patient profile.
     */
    public function show(Patient $patient)
    {
        // Load all relationships for the profile page
        $patient->load([
            'appointments.doctor',
            'files.uploader',
        ]);

        // Get appointment statistics
        $appointmentStats = [
            'total' => $patient->appointments->count(),
            'completed' => $patient->appointments->where('status', 'completed')->count(),
            'upcoming' => $patient->appointments->where('date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])->count(),
        ];

        return view('patients.show', compact('patient', 'appointmentStats'));
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        \Illuminate\Support\Facades\Log::info('Updating Patient ID: ' . $patient->id, $request->all());

        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($patient->photo && Storage::disk('public')->exists($patient->photo)) {
                Storage::disk('public')->delete($patient->photo);
            }
            $path = $request->file('photo')->store('patients', 'public');
            $validated['photo'] = $path;
        }

        $this->patientService->updatePatient($patient, $validated);

        return redirect()->route('patients.index')
            ->with('success', __('Patient updated successfully!'));
    }

    /**
     * Remove the specified patient from storage.
     */
    public function destroy($id, PatientService $service)
    {
        try {
            $service->deletePatient($id);
            return back()->with('success', 'Patient deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Upload a file for the patient.
     */
    public function uploadFile(Request $request, Patient $patient)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'category' => 'required|in:lab_result,xray,mri,prescription,report,other',
            'description' => 'nullable|string|max:1000',
        ]);

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

        return back()->with('success', __('File uploaded successfully!'));
    }

    /**
     * Download a patient file.
     */
    public function downloadFile(Patient $patient, PatientFile $file)
    {
        // Ensure the file belongs to the patient
        if ($file->patient_id !== $patient->id) {
            abort(404);
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    /**
     * Delete a patient file.
     */
    public function deleteFile(Patient $patient, PatientFile $file)
    {
        // Ensure the file belongs to the patient
        if ($file->patient_id !== $patient->id) {
            abort(404);
        }

        // Delete from storage
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        // Delete from database
        $file->delete();

        return back()->with('success', __('File deleted successfully!'));
    }
}
