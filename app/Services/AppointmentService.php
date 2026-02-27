<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * Create a new appointment.
     *
     * @param array $data
     * @return Appointment
     * @throws Exception if there's a scheduling conflict or invalid time
     */
    public function createAppointment(array $data): Appointment
    {
        $doctor = Doctor::findOrFail($data['doctor_id']);
        
        // 1. Check if doctor is on leave
        if (Carbon::parse($data['date'])->isFuture()) {
            $isOnLeave = $doctor->leaves()
                ->whereDate('start_date', '<=', $data['date'])
                ->whereDate('end_date', '>=', $data['date'])
                ->exists();

            if ($isOnLeave) {
                throw new Exception(__('Doctor is on leave on this date.'));
            }

            // 2. Check doctor's schedule
            $dayOfWeek = Carbon::parse($data['date'])->dayOfWeek;
            $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

            if (!$schedule) {
                throw new Exception(__('Doctor is not available on this day.'));
            }

            // 3. Check time slot validity (within working hours)
            $apptTime = Carbon::parse($data['date'] . ' ' . $data['time']);
            $startTime = Carbon::parse($data['date'] . ' ' . $schedule->start_time);
            $endTime = Carbon::parse($data['date'] . ' ' . $schedule->end_time);

            if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
                throw new Exception(__('Selected time is outside doctor\'s working hours.'));
            }
        }

        // 4. Check for conflicts
        $conflict = Appointment::where('doctor_id', $data['doctor_id'])
            ->where('date', $data['date'])
            ->where('time', $data['time'])
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            throw new Exception(__('This time slot is already booked for this doctor.'));
        }

        // Force initial status to 'waiting' according to business rules
        $data['status'] = 'waiting';

        $appointment = Appointment::create($data);

        // Notify Doctor
        try {
            app(\App\Services\NotificationService::class)->notifyDoctor(
                $doctor, 
                'appointment', 
                'New Appointment', 
                "New appointment scheduled on {$data['date']} at {$data['time']}",
                ['date' => $data['date'], 'time' => $data['time']],
                route('appointments.index')
            );
        } catch (\Exception $e) {
            // fail silently if notification fails
        }

        return $appointment;
    }

    /**
     * Update an appointment with scheduling validation.
     *
     * @param Appointment $appointment
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateAppointment(Appointment $appointment, array $data): bool
    {
        $doctor = Doctor::findOrFail($data['doctor_id']);

        // Check availability logic only if date/time/doctor changed
        $timeChanged = $data['date'] !== $appointment->date->format('Y-m-d') || 
                       $data['time'] !== $appointment->time->format('H:i') ||
                       $data['doctor_id'] != $appointment->doctor_id;

        if ($timeChanged && Carbon::parse($data['date'])->isFuture()) {
            
            // 1. Check leave
            $isOnLeave = $doctor->leaves()
                ->whereDate('start_date', '<=', $data['date'])
                ->whereDate('end_date', '>=', $data['date'])
                ->exists();

            if ($isOnLeave) {
                throw new Exception(__('Doctor is on leave on this date.'));
            }
            
            // 2. Check schedule 
            $dayOfWeek = Carbon::parse($data['date'])->dayOfWeek;
            $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

            if (!$schedule) {
                throw new Exception(__('Doctor is not available on this day.'));
            }

             // 3. Check time slot validity (within working hours)
            $apptTime = Carbon::parse($data['date'] . ' ' . $data['time']);
            $startTime = Carbon::parse($data['date'] . ' ' . $schedule->start_time);
            $endTime = Carbon::parse($data['date'] . ' ' . $schedule->end_time);

            if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
                throw new Exception(__('Selected time is outside doctor\'s working hours.'));
            }
        }

        // 4. Check for conflicts (excluding current appointment)
        if ($timeChanged) {
            $conflict = Appointment::where('doctor_id', $data['doctor_id'])
                ->where('date', $data['date'])
                ->where('time', $data['time'])
                ->where('id', '!=', $appointment->id)
                ->whereNotIn('status', ['cancelled'])
                ->exists();

            if ($conflict) {
               throw new Exception(__('This time slot is already booked for this doctor.'));
            }
        }

        return $appointment->update($data);
    }

    /**
     * Update the status of an appointment.
     * Handles specific logic for moving between states like completing a visit.
     *
     * @param mixed $id
     * @param string $status
     * @param array $extraData
     * @return bool
     */
    public function updateStatus($id, string $status, array $extraData = []): bool
    {
        $appointment = $id instanceof Appointment ? $id : Appointment::findOrFail($id);

        $updateData = ['status' => $status];

        switch ($status) {
            case 'in_progress':
                $updateData['started_at'] = now();
                break;
            case 'completed':
                $updateData['completed_at'] = now();
                if (isset($extraData['diagnosis'])) {
                    $updateData['diagnosis'] = $extraData['diagnosis'];
                }
                break;
            case 'checked_in':
                $updateData['checked_in_at'] = now();
                // Notify Doctor
                try {
                    app(\App\Services\NotificationService::class)->notifyDoctor(
                        $appointment->doctor, 
                        'system', 
                        'Patient Checked-In', 
                        "{$appointment->patient->name} has arrived for their appointment.",
                        ['appointment_id' => $appointment->id],
                        route('appointments.show', $appointment->id)
                    );
                } catch (\Exception $e) {}
                break;
        }

        return $appointment->update($updateData);
    }

    /**
     * Delete an appointment safely.
     *
     * @param mixed $id
     * @return void
     * @throws Exception
     */
    public function deleteAppointment($id): void
    {
        $appointment = $id instanceof Appointment ? $id : Appointment::findOrFail($id);

        try {
            DB::beginTransaction();

            // Notify Doctor first if applicable
            try {
                if ($appointment->doctor) {
                     app(\App\Services\NotificationService::class)->notifyDoctor(
                         $appointment->doctor, 
                         'system', 
                         'Appointment Cancelled', 
                         "Appointment with {$appointment->patient->name} on {$appointment->date->format('Y-m-d')} was cancelled.",
                         ['date' => $appointment->date, 'type' => 'cancellation']
                     );
                }
            } catch (\Exception $e) {}

            // Delete related Vitals first
            $appointment->vital()->delete();

            // Delete Appointment
            $appointment->delete();

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
