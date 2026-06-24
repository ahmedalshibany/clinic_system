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

            $appointment->assertLegalTransition(Appointment::STATUS_WAITING);

            $appointmentUpdate = [];
            $appointmentUpdate['status'] = Appointment::STATUS_WAITING;
            if ($appointment->vitals_unlocked) {
                $appointmentUpdate['vitals_unlocked'] = false;
            }
            if (!empty($appointmentUpdate)) {
                $appointment->update($appointmentUpdate);
            }

            try {
                app(NotificationService::class)->notifyDoctor(
                    $appointment->doctor,
                    'appointment',
                    'Patient Ready 🩺',
                    $appointment->patient->name . ' has completed triage and is ready for you.',
                    [
                        'appointment_id' => $appointment->id,
                        'vital_id' => $vital->id,
                        'name' => $appointment->patient->name,
                        'title_key' => 'notification.title_patient_ready',
                        'message_key' => 'notification.message_patient_ready',
                    ],
                    route('dashboard')
                );
            } catch (\Exception $e) {}

            try {
                app(NotificationService::class)->notifyReceptionists(
                    'appointment',
                    'Patient Ready',
                    $appointment->patient->name . ' has completed vitals and is waiting for the doctor.',
                    [
                        'appointment_id' => $appointment->id,
                        'name' => $appointment->patient->name,
                    ],
                    route('dashboard')
                );
            } catch (\Exception $e) {}

            return $vital;
        });
    }
}

