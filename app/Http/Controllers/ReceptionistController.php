<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ReceptionistController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Record payment and activate appointment (pending → paid).
     */
    public function pay(Request $request, Appointment $appointment)
    {
        $this->authorize('pay', $appointment);

        $request->validate([
            'amount'       => 'nullable|numeric|min:0',
            'method'       => 'nullable|string|in:cash,card,bank_transfer,insurance,other',
            'reference'    => 'nullable|string|max:255',
        ]);

        $appointment->loadMissing(['doctor:id,name,consultation_fee']);
        $fee = $request->amount ?? $appointment->fee ?? $appointment->doctor?->consultation_fee ?? 0;

        $this->appointmentService->updateStatus($appointment, 'paid');

        return redirect()->route('invoices.create-from-appointment', $appointment)
            ->with('success', __('messages.paymentRecorded'));
    }

    public function markNoShow(Appointment $appointment)
    {
        $this->authorize('markNoShow', $appointment);

        $this->appointmentService->updateStatus($appointment, 'no_show');

        return redirect()->route('dashboard')
            ->with('success', __('messages.markedNoShow'));
    }

    public function boardData()
    {
        $this->authorize('viewAny', Appointment::class);

        return Cache::remember('board:reception', 30, function () {
            $today = today();
            $tomorrow = today()->addDay();

            $flowMonitor = Appointment::where('date', '>=', $today)
                ->where('date', '<', $tomorrow)
                ->selectRaw("
                    SUM(status = 'paid') AS paid,
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
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'status', 'checked_in_at', 'started_at']);

            $html = View::make('receptionist.partials.live-patients-rows', compact('livePatients'))->render();

            return [
                'flowMonitor' => $flowMonitor,
                'html'        => $html,
                'count'       => $livePatients->count(),
            ];
        });
    }
}
