<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })->orWhereHas('doctor', function ($dq) use ($search) {
                    $dq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Sort
        $sortColumn = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortColumn === 'patient') {
            $query->join('patients', 'appointments.patient_id', '=', 'patients.id')
                  ->orderBy('patients.name', $sortDirection)
                  ->select('appointments.*');
        } elseif ($sortColumn === 'doctor') {
            $query->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
                  ->orderBy('doctors.name', $sortDirection)
                  ->select('appointments.*');
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $appointments = $query->paginate(10)->withQueryString();
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();

        return view('appointments.index', compact('appointments', 'patients', 'doctors'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.create', compact('patients', 'doctors'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
            'time' => 'required|string',
            'type' => 'required|in:Consultation,Checkup,Follow-up,Emergency',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
        ]);

        // Check for conflicts
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('date', $validated['date'])
            ->where('time', $validated['time'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'time' => 'This time slot is already booked for this doctor.'
            ])->withInput();
        }

        Appointment::create($validated);

        return redirect()->route('appointments.index')
            ->with('success', __('Appointment booked successfully!'));
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor']);
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
            'time' => 'required|string',
            'type' => 'required|in:Consultation,Checkup,Follow-up,Emergency',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
        ]);

        // Check for conflicts (excluding current appointment)
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('date', $validated['date'])
            ->where('time', $validated['time'])
            ->where('id', '!=', $appointment->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'time' => 'This time slot is already booked for this doctor.'
            ])->withInput();
        }

        $appointment->update($validated);

        return redirect()->route('appointments.index')
            ->with('success', __('Appointment updated successfully!'));
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', __('Appointment deleted successfully!'));
    }
}
