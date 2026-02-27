<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient:id,name,patient_code,phone', 'doctor:id,name,specialty', 'vital'])
            ->select('id', 'patient_id', 'doctor_id', 'date', 'time', 'type', 'status', 'fee');

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
        $patients = Patient::select('id', 'name')->orderBy('name')->get();
        $doctors = Doctor::select('id', 'name')->where('is_active', true)->orderBy('name')->get();

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
    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->appointmentService->createAppointment($validated);
            return redirect()->route('appointments.index')
                ->with('success', __('Appointment booked successfully!'));
        } catch (\Exception $e) {
            return back()->withErrors(['time' => $e->getMessage()])->withInput();
        }
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
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $validated = $request->validated();

        try {
            $this->appointmentService->updateAppointment($appointment, $validated);
            return redirect()->route('appointments.index')
                ->with('success', __('Appointment updated successfully!'));
        } catch (\Exception $e) {
            return back()->withErrors(['time' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment)
    {
        try {
            $this->appointmentService->deleteAppointment($appointment);
            return redirect()->route('appointments.index')
                ->with('success', __('Appointment deleted successfully!'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting appointment: ' . $e->getMessage());
        }
    }

    // Appointment Lifecycle Methods

    public function checkIn(Appointment $appointment)
    {
        $this->appointmentService->updateStatus($appointment, 'checked_in');
        return back()->with('success', 'Patient checked in successfully.');
    }

    public function startVisit(Appointment $appointment)
    {
        $this->appointmentService->updateStatus($appointment, 'in_progress');
        return back()->with('success', 'Visit started.');
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $request->validate([
            'diagnosis' => 'nullable|string',
        ]);

        $this->appointmentService->updateStatus($appointment, 'completed', ['diagnosis' => $request->diagnosis]);
        return back()->with('success', 'Visit completed and diagnosis saved.');
    }

    public function markNoShow(Appointment $appointment)
    {
        $this->appointmentService->updateStatus($appointment, 'no_show');
        return back()->with('success', 'Marked as No Show.');
    }

    /**
     * Display the appointments calendar.
     */
    public function calendar()
    {
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.calendar', compact('doctors'));
    }

    /**
     * Get appointments for calendar JSON API.
     */
    public function events(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('date', [$request->start, $request->end]);
        }

        $appointments = $query->get()->map(function ($appt) {
            $colors = [
                'scheduled' => '#3b82f6', // blue
                'confirmed' => '#10b981', // green
                'waiting' => '#f59e0b',   // orange
                'in_progress' => '#8b5cf6', // purple
                'completed' => '#6b7280', // gray
                'cancelled' => '#ef4444', // red
                'no_show' => '#000000',   // black
                'pending' => '#64748b',   // slate
            ];

            return [
                'id' => $appt->id,
                'title' => $appt->patient->name . ' (' . $appt->type . ')',
                'start' => $appt->date->format('Y-m-d') . 'T' . $appt->time->format('H:i:s'),
                'backgroundColor' => $colors[$appt->status] ?? '#3b82f6',
                'borderColor' => $colors[$appt->status] ?? '#3b82f6',
                'extendedProps' => [
                    'doctor_name' => $appt->doctor->name,
                    'patient_name' => $appt->patient->name,
                    'status' => ucfirst(str_replace('_', ' ', $appt->status)),
                    'type' => $appt->type,
                ],
                'url' => route('appointments.show', $appt->id)
            ];
        });

        return response()->json($appointments);
    }

    /**
     * Display the appointments queue for today.
     */
    public function queue(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'vital'])
            ->whereDate('date', now()->today());

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->orderBy('time')->get();

        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();

        return view('appointments.queue', compact('appointments', 'doctors'));
    }
}
