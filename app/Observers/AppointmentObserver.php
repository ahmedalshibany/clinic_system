<?php

namespace App\Observers;

use App\Models\Appointment;
use Illuminate\Support\Facades\Cache;

class AppointmentObserver
{
    public function updated(Appointment $appointment): void
    {
        if ($appointment->wasChanged('status') || $appointment->wasChanged('vitals_unlocked')) {
            Cache::forget('board:nurse');
            Cache::forget('board:reception');
            Cache::forget('board:doctor:' . $appointment->doctor_id);
        }
    }
}
