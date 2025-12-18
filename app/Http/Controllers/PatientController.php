<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'blood_type' => 'nullable|string|max:10',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        Patient::create($validated);

        return redirect()->route('patients.index')
            ->with('success', __('Patient added successfully!'));
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient)
    {
        $patient->load('appointments.doctor');
        return view('patients.show', compact('patient'));
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
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'blood_type' => 'nullable|string|max:10',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

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
}
