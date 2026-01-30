<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Vital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NurseController extends Controller
{
    /**
     * Show the form for creating vitals for an appointment.
     */
    public function createVitals(Appointment $appointment)
    {
        // Ensure appointment is in a state that allows vitals (e.g., not cancelled)
        if ($appointment->status == 'cancelled') {
            return back()->with('error', __('Cannot add vitals to a cancelled appointment.'));
        }

        return view('nurse.vitals.create', compact('appointment'));
    }

    /**
     * Store newly created vitals.
     */
    public function storeVitals(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'temperature' => 'required|numeric|min:30|max:45',
            'bp_systolic' => 'required|integer|min:50|max:250',
            'bp_diastolic' => 'required|integer|min:30|max:150',
            'pulse' => 'required|integer|min:30|max:250',
            'respiratory_rate' => 'nullable|integer|min:10|max:60',
            'weight' => 'required|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:10|max:300',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $appointment) {
            Vital::create([
                'appointment_id' => $appointment->id,
                'created_by' => Auth::id(),
                'temperature' => $validated['temperature'],
                'bp_systolic' => $validated['bp_systolic'],
                'bp_diastolic' => $validated['bp_diastolic'],
                'pulse' => $validated['pulse'],
                'respiratory_rate' => $validated['respiratory_rate'] ?? null,
                'weight' => $validated['weight'],
                'height' => $validated['height'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'notes' => $validated['notes'],
            ]);

            // Optional: Update appointment status to 'waiting' if it was pending/confirmed
            if (in_array($appointment->status, ['pending', 'confirmed', 'checked_in'])) {
                $appointment->update(['status' => 'waiting']);
            }
        });

        return redirect()->route('dashboard')
            ->with('success', __('Vitals recorded successfully.'));
    }
}
