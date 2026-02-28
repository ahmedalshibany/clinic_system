<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
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
        // Ensure appointment is in a state that allows vitals (e.g., not cancelled)
        if ($appointment->status === 'cancelled') {
            return back()->with('error', __('Cannot add vitals to a cancelled appointment.'));
        }

        return view('nurse.vitals.create', compact('appointment'));
    }

    /**
     * Store newly created vitals.
     */
    public function storeVitals(StoreVitalRequest $request, Appointment $appointment)
    {
        try {
            $this->vitalService->recordVitals($appointment, $request->validated());

            return redirect()->route('dashboard')
                ->with('success', __('Vitals recorded successfully.'));
        } catch (\Exception $e) {
            Log::error('Failed to record vitals: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to record vitals. Please try again.'));
        }
    }
}
