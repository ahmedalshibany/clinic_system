<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ReceptionistController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function checkIn(Request $request, Appointment $appointment)
    {
        $this->authorize('checkIn', $appointment);

        $this->appointmentService->updateStatus($appointment, Appointment::STATUS_CHECKED_IN);

        $position = Appointment::whereDate('date', now()->today())
            ->where('time', '<', $appointment->time)
            ->whereIn('status', [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_WAITING])
            ->count() + 1;

        if ($request->expectsJson()) {
            $appointment->load(['patient:id,name,patient_code,phone', 'doctor:id,name']);
            return response()->json([
                'success' => true,
                'message' => __('messages.patientCheckedIn'),
                'position' => $position,
                'appointment' => $appointment,
            ]);
        }

        session()->flash('info', __('messages.queuePositionInfo', ['position' => $position]));

        return redirect()->route('dashboard')
            ->with('success', __('messages.patientCheckedIn'));
    }

    public function markNoShow(Appointment $appointment)
    {
        $this->authorize('markNoShow', $appointment);

        $this->appointmentService->updateStatus($appointment, Appointment::STATUS_NO_SHOW);

        return redirect()->route('dashboard')
            ->with('success', __('messages.markedNoShow'));
    }

    public function boardData()
    {
        $this->authorize('viewAny', Appointment::class);
        $today = today();
        $tomorrow = today()->addDay();

        $flowMonitor = Appointment::where('date', '>=', $today)
            ->where('date', '<', $tomorrow)
            ->selectRaw("
                SUM(status = 'checked_in') AS checked_in,
                SUM(status = 'waiting') AS waiting,
                SUM(status = 'in_progress') AS in_progress,
                SUM(status = 'completed') AS completed,
                SUM(status = 'cancelled') AS cancelled,
                SUM(status = 'no_show') AS no_show
            ")->first()->toArray();

        $livePatients = Appointment::with(['patient:id,name,patient_code,phone', 'doctor:id,name'])
            ->where('date', '>=', $today)
            ->where('date', '<', $tomorrow)
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_WAITING,
                Appointment::STATUS_IN_PROGRESS,
            ])
            ->orderBy('time')
            ->get();

        $html = View::make('receptionist.partials.live-patients-rows', compact('livePatients'))->render();

        return response()->json([
            'flowMonitor' => $flowMonitor,
            'html'        => $html,
            'count'       => $livePatients->count(),
        ]);
    }
}
