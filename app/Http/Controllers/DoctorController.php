<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Doctor::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 8);
        $doctors = $query->paginate($perPage);

        return response()->json($doctors);
    }

    /**
     * Store a newly created doctor.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
            'working_days' => 'nullable|array',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'consultation_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $doctor = Doctor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Doctor created successfully',
            'data' => $doctor
        ], 201);
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor): JsonResponse
    {
        $doctor->load('appointments.patient');
        
        return response()->json([
            'success' => true,
            'data' => $doctor
        ]);
    }

    /**
     * Update the specified doctor.
     */
    public function update(Request $request, Doctor $doctor): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'specialty' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
            'working_days' => 'nullable|array',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'consultation_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $doctor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Doctor updated successfully',
            'data' => $doctor
        ]);
    }

    /**
     * Remove the specified doctor.
     */
    public function destroy(Doctor $doctor): JsonResponse
    {
        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor deleted successfully'
        ]);
    }

    /**
     * Search doctors for autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
        $doctors = Doctor::active()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'specialty', 'phone']);

        return response()->json($doctors);
    }

    /**
     * Get doctor's schedule/appointments for a specific date.
     */
    public function schedule(Request $request, Doctor $doctor): JsonResponse
    {
        $date = $request->get('date', now()->toDateString());
        
        $appointments = $doctor->appointments()
            ->with('patient:id,name,phone')
            ->where('date', $date)
            ->orderBy('time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
}
