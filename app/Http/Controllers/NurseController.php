<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Vital;
use App\Services\VitalService;
use App\Http\Requests\Vitals\StoreVitalRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class NurseController extends Controller
{
    protected VitalService $vitalService;

    public function __construct(VitalService $vitalService)
    {
        $this->vitalService = $vitalService;
    }

    public function createVitals(Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        if (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN, Appointment::STATUS_SCHEDULED]) && !$appointment->vitals_unlocked) {
            return back()->with('error', __('messages.vitalsNotAllowed'))
                ->with('warning', __('messages.vitalsLocked'));
        }
        return view('nurse.vitals.create', compact('appointment'));
    }

    public function storeVitals(StoreVitalRequest $request, Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        try {
            $this->vitalService->recordVitals($appointment, $request->validated());
            return redirect()->route('dashboard')
                ->with('success', __('messages.vitalsRecorded'));
        } catch (\Exception $e) {
            Log::error('Failed to record vitals: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.vitalsFailed'));
        }
    }

    /**
     * AJAX: Save vitals from inline dashboard form.
     */
    public function storeVitalsAjax(StoreVitalRequest $request, Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        try {
            if (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN, Appointment::STATUS_SCHEDULED]) && !$appointment->vitals_unlocked) {
                return response()->json(['success' => false, 'message' => __('messages.vitalsNotAllowed')], 422);
            }
            $this->vitalService->recordVitals($appointment, $request->validated());
            return response()->json(['success' => true, 'message' => __('messages.vitalsRecorded')]);
        } catch (\Exception $e) {
            Log::error('Failed to record vitals (AJAX): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('messages.vitalsFailed')], 500);
        }
    }

    /**
     * AJAX: Return today's triage queue (confirmed/checked_in) as JSON.
     */
    public function triageQueueApi()
    {
        $triageQueue = Appointment::with(['patient:id,name', 'doctor:id,name'])
            ->whereDate('date', today())
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->orderBy('time')
            ->get(['id', 'patient_id', 'doctor_id', 'time', 'status']);

        $waitingList = Appointment::with(['patient:id,name', 'doctor:id,name'])
            ->whereDate('date', today())
            ->where('status', 'waiting')
            ->orderBy('time')
            ->get(['id', 'patient_id', 'doctor_id', 'time', 'status']);

        return response()->json([
            'triageQueue' => $triageQueue->map(fn($a) => [
                'id'            => $a->id,
                'time'          => $a->time->format('H:i'),
                'patient_name'  => $a->patient->name,
                'doctor_name'   => $a->doctor->name,
                'status'        => $a->status,
                'status_label'  => $a->status === 'checked_in' ? __('messages.checked_in') : __('Confirmed'),
            ]),
            'waitingList' => $waitingList->map(fn($a) => [
                'id'            => $a->id,
                'time'          => $a->time->format('H:i'),
                'patient_name'  => $a->patient->name,
                'doctor_name'   => $a->doctor->name,
                'status'        => $a->status,
            ]),
            'triageCount'  => $triageQueue->count(),
            'waitingCount' => $waitingList->count(),
        ]);
    }
}
