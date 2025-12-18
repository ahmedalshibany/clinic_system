<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::with(['patient:id,name,phone', 'doctor:id,name,specialty']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('doctor', function ($dq) use ($search) {
                    $dq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Filter by doctor
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by patient
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Sorting
        $sortField = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortField === 'patientName') {
            $query->join('patients', 'appointments.patient_id', '=', 'patients.id')
                  ->orderBy('patients.name', $sortDirection)
                  ->select('appointments.*');
        } elseif ($sortField === 'doctorName') {
            $query->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
                  ->orderBy('doctors.name', $sortDirection)
                  ->select('appointments.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Secondary sort by time
        $query->orderBy('time', 'asc');

        // Pagination
        $perPage = $request->get('per_page', 10);
        $appointments = $query->paginate($perPage);

        return response()->json($appointments);
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'type' => 'required|in:Consultation,Checkup,Follow-up,Emergency',
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
        ]);

        // Check for conflicting appointments
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('date', $validated['date'])
            ->where('time', $validated['time'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor already has an appointment at this time'
            ], 422);
        }

        $appointment = Appointment::create($validated);
        $appointment->load(['patient:id,name,phone', 'doctor:id,name,specialty']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment created successfully',
            'data' => $appointment
        ], 201);
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        $appointment->load(['patient', 'doctor']);
        
        return response()->json([
            'success' => true,
            'data' => $appointment
        ]);
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patients,id',
            'doctor_id' => 'sometimes|required|exists:doctors,id',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required|date_format:H:i',
            'type' => 'sometimes|required|in:Consultation,Checkup,Follow-up,Emergency',
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
        ]);

        // Check for conflicting appointments if date/time changed
        if (isset($validated['date']) || isset($validated['time'])) {
            $date = $validated['date'] ?? $appointment->date;
            $time = $validated['time'] ?? $appointment->time;
            $doctorId = $validated['doctor_id'] ?? $appointment->doctor_id;

            $conflict = Appointment::where('doctor_id', $doctorId)
                ->where('date', $date)
                ->where('time', $time)
                ->where('status', '!=', 'cancelled')
                ->where('id', '!=', $appointment->id)
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor already has an appointment at this time'
                ], 422);
            }
        }

        $appointment->update($validated);
        $appointment->load(['patient:id,name,phone', 'doctor:id,name,specialty']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated successfully',
            'data' => $appointment
        ]);
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully'
        ]);
    }

    /**
     * Get today's appointments.
     */
    public function today(Request $request): JsonResponse
    {
        $appointments = Appointment::with(['patient:id,name,phone', 'doctor:id,name,specialty'])
            ->today()
            ->orderBy('time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Get recent appointments for dashboard.
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        
        $appointments = Appointment::with(['patient:id,name', 'doctor:id,name,specialty'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Get appointment statistics for dashboard.
     */
    public function stats(Request $request): JsonResponse
    {
        $filter = $request->get('filter', 'all');
        
        $query = Appointment::query();
        
        if ($filter === 'today') {
            $query->today();
        } elseif ($filter === 'week') {
            $query->thisWeek();
        }

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->pending()->count(),
            'confirmed' => (clone $query)->confirmed()->count(),
            'completed' => (clone $query)->completed()->count(),
            'cancelled' => (clone $query)->cancelled()->count(),
            'today' => Appointment::today()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get weekly trend data for charts.
     */
    public function weeklyTrend(): JsonResponse
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Appointment::where('date', $date->toDateString())->count();
            $data[] = [
                'date' => $date->format('D'),
                'count' => $count
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
