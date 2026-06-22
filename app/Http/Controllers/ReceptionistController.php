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

    public function checkIn(Appointment $appointment)
    {
        $this->authorize('checkIn', $appointment);

        $this->appointmentService->updateStatus($appointment, Appointment::STATUS_CHECKED_IN);

        $position = Appointment::whereDate('date', now()->today())
            ->where('time', '<', $appointment->time)
            ->whereIn('status', [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_WAITING])
            ->count() + 1;

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
}
