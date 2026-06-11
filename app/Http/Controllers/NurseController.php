<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Vital;
use App\Services\VitalService;
use App\Http\Requests\Vitals\StoreVitalRequest;
use Illuminate\Support\Facades\Log;

class NurseController extends Controller
{
    protected VitalService $vitalService;

    public function __construct(VitalService $vitalService)
    {
        $this->vitalService = $vitalService;
    }

    /**
     * Show the form for creating vitals for an appointment.
     */
    public function createVitals(Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        if ($appointment->status !== 'pending' && !$appointment->vitals_unlocked) {
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

}
