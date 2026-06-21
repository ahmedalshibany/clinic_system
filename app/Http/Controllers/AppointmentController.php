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
            $appointment = $this->appointmentService->createAppointment($validated);

            $doctorApptsToday = Appointment::where('doctor_id', $validated['doctor_id'])
                ->whereDate('date', $validated['date'])
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->count();

            $schedule = \App\Models\DoctorSchedule::where('doctor_id', $validated['doctor_id'])
                ->where('day_of_week', \Carbon\Carbon::parse($validated['date'])->dayOfWeek)
                ->first();

            if ($schedule && $schedule->max_appointments && $doctorApptsToday >= $schedule->max_appointments * 0.8) {
                session()->flash('warning', __('messages.doctorNearCapacity'));
            }

            return redirect()->route('appointments.index')
                ->with('success', __('messages.appointmentBooked'));
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
                ->with('success', __('messages.appointmentUpdated'));
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
                ->with('success', __('messages.appointmentDeleted'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.appointmentDeleteError') . ' ' . $e->getMessage());
        }
    }

    // Appointment Lifecycle Methods

    public function checkIn(Appointment $appointment)
    {
        $this->authorize('checkIn', $appointment);
        $this->appointmentService->updateStatus($appointment, 'checked_in');

        $position = Appointment::whereDate('date', now()->today())
            ->where('time', '<', $appointment->time)
            ->whereIn('status', ['checked_in', 'waiting'])
            ->count() + 1;

        session()->flash('info', __('messages.queuePositionInfo', ['position' => $position]));

        return back()->with('success', __('messages.patientCheckedIn'));
    }

    public function startVisit(Appointment $appointment)
    {
        $this->authorize('startVisit', $appointment);
        $this->appointmentService->updateStatus($appointment, 'in_progress');
        return back()->with('success', __('messages.visitStarted'));
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $this->authorize('complete', $appointment);
        $request->validate([
            'diagnosis' => 'nullable|string',
        ]);

        $this->appointmentService->updateStatus($appointment, 'completed', ['diagnosis' => $request->diagnosis]);

        $outstandingBalance = $appointment->patient->invoices()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum(\Illuminate\Support\Facades\DB::raw('total - paid_amount'));

        if ($outstandingBalance > 0) {
            session()->flash('warning', __('messages.patientOutstandingBalance', ['amount' => number_format($outstandingBalance, 2)]));
        }

        return back()->with('success', __('messages.visitCompleted'));
    }

    public function markNoShow(Appointment $appointment)
    {
        $this->authorize('markNoShow', $appointment);
        $this->appointmentService->updateStatus($appointment, 'no_show');
        return back()->with('success', __('messages.markedNoShow'));
    }

    public function reopenVitals(Appointment $appointment)
    {
        Gate::authorize('reopenVitals', User::class);

        $allowed = ['checked_in', 'waiting'];
        if (!in_array($appointment->status, $allowed)) {
            return back()->with('error', __('messages.vitalsNotAllowed'));
        }

        $this->appointmentService->reopenVitals($appointment);
        return back()->with('success', __('messages.vitalsReopened'));
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
                'checked_in' => '#06b6d4', // cyan
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
                'url' => route('appointments.show', $appt->id) . '?from=calendar'
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

        if ($appointments->isEmpty()) {
            session()->flash('info', __('messages.queueEmptyInfo'));
        }

        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();

        return view('appointments.queue', compact('appointments', 'doctors'));
    }
}
