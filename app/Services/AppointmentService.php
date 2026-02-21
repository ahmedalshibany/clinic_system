<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Check doctor availability and return validated state or throw ValidationException.
     *
     * @param Doctor $doctor
     * @param string $date
     * @param string $time
     * @param int|null $excludeAppointmentId
     * @throws ValidationException
     */
    public function validateAvailability(Doctor $doctor, string $date, string $time, ?int $excludeAppointmentId = null): void
    {
        $parsedDate = Carbon::parse($date);

        if ($parsedDate->isFuture()) {
            // 1. Check if doctor is on leave
            $isOnLeave = $doctor->leaves()
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();

            if ($isOnLeave) {
                throw ValidationException::withMessages(['date' => __('Doctor is on leave on this date.')]);
            }

            // 2. Check doctor's schedule
            $dayOfWeek = $parsedDate->dayOfWeek;
            $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

            if (!$schedule) {
                throw ValidationException::withMessages(['date' => __('Doctor is not available on this day.')]);
            }

            // 3. Check time slot validity (within working hours)
            $apptTime = Carbon::parse($date . ' ' . $time);
            $startTime = Carbon::parse($date . ' ' . $schedule->start_time);
            $endTime = Carbon::parse($date . ' ' . $schedule->end_time);

            if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
                throw ValidationException::withMessages(['time' => __('Selected time is outside doctor\'s working hours.')]);
            }
        }

        // 4. Check for conflicts
        $query = Appointment::where('doctor_id', $doctor->id)
            ->where('date', $date)
            ->where('time', $time)
            ->whereNotIn('status', ['cancelled']);

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['time' => __('This time slot is already booked for this doctor.')]);
        }
    }
}
