<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Vital;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VitalService
{
    /**
     * Record vitals for a given appointment.
     */
    public function recordVitals(Appointment $appointment, array $data): Vital
    {
        return DB::transaction(function () use ($appointment, $data) {
            $vital = Vital::create([
                'appointment_id' => $appointment->id,
                'created_by' => Auth::id(),
                'temperature' => $data['temperature'],
                'bp_systolic' => $data['bp_systolic'],
                'bp_diastolic' => $data['bp_diastolic'],
                'pulse' => $data['pulse'],
                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                'weight' => $data['weight'],
                'height' => $data['height'] ?? null,
                'oxygen_saturation' => $data['oxygen_saturation'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Optional: Update appointment status to 'waiting' if it was pending/confirmed
            if (in_array($appointment->status, ['pending', 'confirmed', 'checked_in'])) {
                $appointment->update(['status' => 'waiting']);
            }

            return $vital;
        });
    }
}
