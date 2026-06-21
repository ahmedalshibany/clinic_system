<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReceptionistController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function dashboard()
    {
        $triageBoard = Appointment::with(['patient:id,name,patient_code,phone', 'doctor:id,name'])
            ->whereDate('date', today())
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, Appointment::STATUS_SCHEDULED])
            ->orderBy('time')
            ->get();

        $allToday = Appointment::whereDate('date', today())->get();

        $flowMonitor = [
            'checked_in'  => $allToday->where('status', Appointment::STATUS_CHECKED_IN)->count(),
            'waiting'     => $allToday->where('status', Appointment::STATUS_WAITING)->count(),
            'in_progress' => $allToday->where('status', Appointment::STATUS_IN_PROGRESS)->count(),
            'completed'   => $allToday->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled'   => $allToday->where('status', Appointment::STATUS_CANCELLED)->count(),
            'no_show'     => $allToday->where('status', Appointment::STATUS_NO_SHOW)->count(),
        ];

        $livePatients = Appointment::with(['patient:id,name,patient_code,phone', 'doctor:id,name'])
            ->whereDate('date', today())
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_WAITING,
                Appointment::STATUS_IN_PROGRESS,
            ])
            ->orderBy('time')
            ->get();

        return view('receptionist.dashboard', compact(
            'triageBoard',
            'flowMonitor',
            'livePatients'
        ));
    }

    public function checkIn(Appointment $appointment)
    {
        $this->authorize('checkIn', $appointment);

        $this->appointmentService->updateStatus($appointment, Appointment::STATUS_CHECKED_IN);

        $position = Appointment::whereDate('date', now()->today())
            ->where('time', '<', $appointment->time)
            ->whereIn('status', [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_WAITING])
            ->count() + 1;

        session()->flash('info', __('messages.queuePositionInfo', ['position' => $position]));

        return redirect()->route('receptionist.dashboard')
            ->with('success', __('messages.patientCheckedIn'));
    }

    public function markNoShow(Appointment $appointment)
    {
        $this->authorize('markNoShow', $appointment);

        $this->appointmentService->updateStatus($appointment, Appointment::STATUS_NO_SHOW);

        return redirect()->route('receptionist.dashboard')
            ->with('success', __('messages.markedNoShow'));
    }
}
