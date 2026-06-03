<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use Illuminate\Support\Facades\Gate;

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
        $this->authorize('viewAny', Appointment::class);
        $appointments = $this->appointmentService->getAllAppointments($request->all());
        $doctors = Doctor::select('id', 'name')->where('is_active', true)->orderBy('name')->get();

        return view('appointments.index', compact('appointments', 'doctors'));
    }

    public function create()
    {
        $this->authorize('create', Appointment::class);
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.create', compact('patients', 'doctors'));
    }

    public function store(StoreAppointmentRequest $request)
    {
        $this->authorize('create', Appointment::class);
        $validated = $request->validated();

        try {
            $this->appointmentService->createAppointment($validated);
            return redirect()->route('appointments.index')
                ->with('success', __('Appointment booked successfully!'));
        } catch (\Exception $e) {
            return back()->withErrors(['time' => $e->getMessage()])->withInput();
        }
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);
        $appointment->load(['patient', 'doctor']);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        $validated = $request->validated();

        try {
            $this->appointmentService->updateAppointment($appointment, $validated);
            return redirect()->route('appointments.index')
                ->with('success', __('Appointment updated successfully!'));
        } catch (\Exception $e) {
            return back()->withErrors(['time' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);
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
        $this->authorize('checkIn', $appointment);
        $this->appointmentService->updateStatus($appointment, 'checked_in');
        return back()->with('success', 'Patient checked in successfully.');
    }

    public function startVisit(Appointment $appointment)
    {
        $this->authorize('startVisit', $appointment);
        $this->appointmentService->updateStatus($appointment, 'in_progress');
        return back()->with('success', 'Visit started.');
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $this->authorize('complete', $appointment);
        $request->validate([
            'diagnosis' => 'nullable|string',
        ]);

        $this->appointmentService->updateStatus($appointment, 'completed', ['diagnosis' => $request->diagnosis]);
        return back()->with('success', 'Visit completed and diagnosis saved.');
    }

    public function markNoShow(Appointment $appointment)
    {
        $this->authorize('markNoShow', $appointment);
        $this->appointmentService->updateStatus($appointment, 'no_show');
        return back()->with('success', 'Marked as No Show.');
    }

    public function reopenVitals(Appointment $appointment)
    {
        Gate::authorize('reopenVitals', User::class);
        $appointment->update([
            'vitals_unlocked' => true,
            'status' => 'pending',
        ]);
        return back()->with('success', 'Vitals re-opened for nurse triage.');
    }

    /**
     * Display the appointments calendar.
     */
    public function calendar()
    {
        $this->authorize('viewAny', Appointment::class);
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.calendar', compact('doctors'));
    }

    /**
     * Get appointments for calendar JSON API.
     */
    public function events(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);
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
        $this->authorize('viewAny', Appointment::class);
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
