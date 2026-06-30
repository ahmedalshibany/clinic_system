<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AppointmentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DoctorDashboardController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function startVisit(Appointment $appointment)
    {
        $this->authorize('startVisit', $appointment);

        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_if($appointment->doctor_id !== $doctor->id, 403);

        $this->appointmentService->updateStatus($appointment, 'in_progress');

        return redirect()->route('dashboard')
            ->with('success', __('messages.visitStarted'));
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $this->authorize('complete', $appointment);

        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_if($appointment->doctor_id !== $doctor->id, 403);

        $request->validate([
            'diagnosis' => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $this->appointmentService->updateStatus($appointment, 'completed', [
            'diagnosis' => $request->diagnosis,
        ]);

        if ($request->filled('notes')) {
            $appointment->update(['notes' => $request->notes]);
        }

        return redirect()->route('dashboard')
            ->with('success', __('messages.visitCompleted'));
    }

    /**
     * Doctor requests vitals for a triaged patient (checked_in).
     * Notifies the nurse to record vitals.
     */
    public function requestVitals(Appointment $appointment)
    {
        $this->authorize('requestVitals', $appointment);

        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_if($appointment->doctor_id !== $doctor->id, 403);

        $appointment->loadMissing(['patient:id,name']);
        $appointment->update(['vitals_unlocked' => true]);

        try {
            app(NotificationService::class)->notifyNurses(
                'vitals',
                __('messages.notification.title_vitals_requested'),
                __('messages.notification.message_vitals_requested', ['doctor' => $doctor->name, 'patient' => $appointment->patient->name]),
                [
                    'appointment_id' => $appointment->id,
                    'patient_name' => $appointment->patient->name,
                ],
                route('dashboard')
            );
        } catch (\Exception $e) {}

        return redirect()->route('dashboard')
            ->with('success', __('messages.vitalsRequested'));
    }

    /**
     * Doctor routes patient directly to the consultation queue (no vitals needed).
     */
    public function directToRoom(Appointment $appointment)
    {
        $this->authorize('directToRoom', $appointment);

        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_if($appointment->doctor_id !== $doctor->id, 403);

        $this->appointmentService->updateStatus($appointment, 'in_progress');

        return redirect()->route('dashboard')
            ->with('success', __('messages.visitStarted'));
    }

    /**
     * Doctor requests vitals during an active consultation (in_progress).
     */
    public function requestVitalsDuringSession(Request $request, Appointment $appointment)
    {
        $this->authorize('requestVitals', $appointment);

        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_if($appointment->doctor_id !== $doctor->id, 403);

        $appointment->loadMissing(['patient:id,name']);
        $appointment->update(['vitals_unlocked' => true]);

        try {
            app(NotificationService::class)->notifyNurses(
                'vitals',
                __('messages.notification.title_vitals_requested_session'),
                __('messages.notification.message_vitals_requested_session', ['doctor' => $doctor->name, 'patient' => $appointment->patient->name]),
                [
                    'appointment_id' => $appointment->id,
                    'patient_name' => $appointment->patient->name,
                ],
                route('dashboard')
            );
        } catch (\Exception $e) {}

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('messages.vitalsRequested')]);
        }

        return redirect()->route('dashboard')
            ->with('success', __('messages.vitalsRequested'));
    }

    public function showAppointment(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'vital', 'doctor']);

        return view('doctor.appointments.show', compact('appointment'));
    }

    public function boardPartial()
    {
        $this->authorize('viewAny', Appointment::class);

        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        return Cache::remember('board:doctor:' . $doctor->id, 30, function () use ($doctor) {
            $today = today();
            $tomorrow = today()->addDay();

            $triageQueue = Appointment::with([
                    'patient:id,name,patient_code',
                    'vital:appointment_id,temperature,bp_systolic,bp_diastolic,pulse,weight,height',
                ])
                ->where('doctor_id', $doctor->id)
                ->where('date', '>=', $today)
                ->where('date', '<', $tomorrow)
                ->whereIn('status', ['paid', 'checked_in'])
                ->orderBy('time')
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'status', 'checked_in_at']);

            $readyQueue = Appointment::with([
                    'patient:id,name,patient_code',
                    'vital:appointment_id,temperature,bp_systolic,bp_diastolic,pulse,weight,height',
                ])
                ->where('doctor_id', $doctor->id)
                ->where('date', '>=', $today)
                ->where('date', '<', $tomorrow)
                ->where('status', 'waiting')
                ->orderBy('time')
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'status', 'checked_in_at']);

            $inSession = Appointment::with([
                    'patient:id,name,patient_code',
                    'vital:appointment_id,temperature,bp_systolic,bp_diastolic,pulse,weight,height',
                ])
                ->where('doctor_id', $doctor->id)
                ->where('date', '>=', $today)
                ->where('date', '<', $tomorrow)
                ->where('status', 'in_progress')
                ->first(['id', 'patient_id', 'doctor_id', 'time', 'status', 'checked_in_at']);

            $html = view('doctor.partials._waiting_queue', compact('triageQueue', 'readyQueue', 'inSession'))->render();

            return [
                'html'  => $html,
                'count' => $triageQueue->count() + $readyQueue->count(),
            ];
        });
    }
}
