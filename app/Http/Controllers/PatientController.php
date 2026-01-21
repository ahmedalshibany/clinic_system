<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Models\PatientFile;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
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
    /**
     * Store a newly created patient.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'occupation' => 'nullable|string|max:100',
            'blood_type' => 'nullable|string|max:10',
            'medical_history' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'previous_surgeries' => 'nullable|string',
            'family_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_relation' => 'nullable|string|max:50',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:50',
            'insurance_expiry' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,deceased',
            'photo' => 'nullable|image|max:2048', // 2MB Max
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('patients', 'public');
            $validated['photo'] = $path;
        }

        $patient = Patient::create($validated);

        return redirect()->route('patients.show', $patient)
            ->with('success', __('Patient added successfully!'));
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
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'occupation' => 'nullable|string|max:100',
            'blood_type' => 'nullable|string|max:10',
            'medical_history' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'previous_surgeries' => 'nullable|string',
            'family_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_relation' => 'nullable|string|max:50',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:50',
            'insurance_expiry' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,deceased',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($patient->photo && Storage::disk('public')->exists($patient->photo)) {
                Storage::disk('public')->delete($patient->photo);
            }
            $path = $request->file('photo')->store('patients', 'public');
            $validated['photo'] = $path;
        }

        $patient->update($validated);

        return redirect()->route('patients.index')
            ->with('success', __('Patient updated successfully!'));
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', __('Patient deleted successfully!'));
    }

    /**
     * Upload a file for the patient.
     */


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
