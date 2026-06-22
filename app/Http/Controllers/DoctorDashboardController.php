<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $this->appointmentService->updateStatus($appointment, Appointment::STATUS_IN_PROGRESS);

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

        $this->appointmentService->updateStatus($appointment, Appointment::STATUS_COMPLETED, [
            'diagnosis' => $request->diagnosis,
        ]);

        if ($request->filled('notes')) {
            $appointment->update(['notes' => $request->notes]);
        }

        $outstandingBalance = $appointment->patient->invoices()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum(DB::raw('total - amount_paid'));

        if ($outstandingBalance > 0) {
            session()->flash('warning', __('messages.patientOutstandingBalance', [
                'amount' => number_format($outstandingBalance, 2),
            ]));
        }

        return redirect()->route('dashboard')
            ->with('success', __('messages.visitCompleted'));
    }
}
