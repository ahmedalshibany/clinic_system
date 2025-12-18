<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Patient::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $patients = $query->paginate($perPage);

        return response()->json($patients);
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'blood_type' => 'nullable|string|max:5',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully',
            'data' => $patient
        ], 201);
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient): JsonResponse
    {
        $patient->load('appointments.doctor');
        
        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'age' => 'sometimes|required|integer|min:0|max:150',
            'gender' => 'sometimes|required|in:male,female',
            'phone' => 'sometimes|required|string|max:20',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'blood_type' => 'nullable|string|max:5',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        $patient->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully',
            'data' => $patient
        ]);
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully'
        ]);
    }

    /**
     * Search patients for autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
        $patients = Patient::where('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'phone', 'age', 'gender']);

        return response()->json($patients);
    }

    /**
     * Get patient's appointment history.
     */
    public function history(Patient $patient): JsonResponse
    {
        $appointments = $patient->appointments()
            ->with('doctor:id,name,specialty')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
}
