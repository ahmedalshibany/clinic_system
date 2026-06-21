<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * Get paginated, filtered list of appointments.
     */
    public function getAllAppointments(array $filters): LengthAwarePaginator
    {
        $query = Appointment::with(['patient:id,name,patient_code,phone', 'doctor:id,name,specialty', 'vital'])
            ->select('id', 'patient_id', 'doctor_id', 'date', 'time', 'type', 'status', 'fee');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })->orWhereHas('doctor', function ($dq) use ($search) {
                    $dq->where('name', 'like', "%{$search}%");
                });
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        $sortColumn = $filters['sort'] ?? 'date';
        $sortDirection = $filters['direction'] ?? 'desc';

        if ($sortColumn === 'patient') {
            $query->join('patients', 'appointments.patient_id', '=', 'patients.id')
                  ->orderBy('patients.name', $sortDirection)
                  ->select('appointments.*');
        } elseif ($sortColumn === 'doctor') {
            $query->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
                  ->orderBy('doctors.name', $sortDirection)
                  ->select('appointments.*');
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query->paginate(10)->withQueryString();
    }

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
        $isOnLeave = $doctor->leaves()
            ->whereDate('start_date', '<=', $data['date'])
            ->whereDate('end_date', '>=', $data['date'])
            ->exists();

        if ($isOnLeave) {
            throw new Exception(__('messages.doctorOnLeave'));
        }

        // 2. Check doctor's schedule
        $dayOfWeek = Carbon::parse($data['date'])->dayOfWeek;
        $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

        if (!$schedule) {
            throw new Exception(__('messages.doctorUnavailable'));
        }

        // 3. Check time slot validity (within working hours)
        $apptTime = Carbon::parse($data['date'] . ' ' . $data['time']);
        $startTime = Carbon::parse($data['date'] . ' ' . $schedule->start_time);
        $endTime = Carbon::parse($data['date'] . ' ' . $schedule->end_time);

        if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
            throw new Exception(__('messages.timeOutsideHours'));
        }

        // 3a. Validate slot alignment against the schedule's actual slot_duration
        $slotDuration = $schedule->slot_duration;
        $minutesSinceStart = $apptTime->diffInMinutes($startTime);
        if ($minutesSinceStart % $slotDuration !== 0) {
            throw new Exception(__('messages.slotMustBe15min'));
        }

        DB::beginTransaction();

        try {
            // 4. Check for conflicts ط·آ£ط¢آ¢ط£آ¢أ¢â‚¬ع‘ط¢آ¬ط£آ¢أ¢â€ڑآ¬أ¢â‚¬إ’ duration-aware overlap + no_show/cancelled unlock
            $slotDuration = (int) Setting::get('appointment_slot_duration', 30);
            $newStart = Carbon::parse($data['date'] . ' ' . $data['time']);
            $newEnd = (clone $newStart)->addMinutes($slotDuration);

            $hasOverlap = Appointment::where('doctor_id', $data['doctor_id'])
                ->whereDate('date', $data['date'])
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->lockForUpdate()
                ->get()
                ->contains(function ($existing) use ($newStart, $newEnd) {
                    $existingStart = Carbon::parse($existing->date->format('Y-m-d') . ' ' . $existing->time->format('H:i:s'));
                    $existingEnd = (clone $existingStart)->addMinutes((int) Setting::get('appointment_slot_duration', 30));
                    return max($newStart->timestamp, $existingStart->timestamp) < min($newEnd->timestamp, $existingEnd->timestamp);
                });

            if ($hasOverlap) {
                throw new Exception(__('messages.timeSlotBooked'));
            }

            // Force initial status to 'waiting' according to business rules
            $data['status'] = 'waiting';

            $appointment = Appointment::create($data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

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

        if ($timeChanged) {
            
            // 1. Check leave
            $isOnLeave = $doctor->leaves()
                ->whereDate('start_date', '<=', $data['date'])
                ->whereDate('end_date', '>=', $data['date'])
                ->exists();

            if ($isOnLeave) {
                throw new Exception(__('messages.doctorOnLeave'));
            }
            
            // 2. Check schedule 
            $dayOfWeek = Carbon::parse($data['date'])->dayOfWeek;
            $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

            if (!$schedule) {
                throw new Exception(__('messages.doctorUnavailable'));
            }

             // 3. Check time slot validity (within working hours)
            $apptTime = Carbon::parse($data['date'] . ' ' . $data['time']);
            $startTime = Carbon::parse($data['date'] . ' ' . $schedule->start_time);
            $endTime = Carbon::parse($data['date'] . ' ' . $schedule->end_time);

            if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
                throw new Exception(__('messages.timeOutsideHours'));
            }

            // 3a. Validate slot alignment against the schedule's actual slot_duration
            $slotDuration = $schedule->slot_duration;
            $minutesSinceStart = $apptTime->diffInMinutes($startTime);
            if ($minutesSinceStart % $slotDuration !== 0) {
                throw new Exception(__('messages.slotMustBe15min'));
            }
        }

        // 4. Check for conflicts with lock (excluding current appointment) ط·آ£ط¢آ¢ط£آ¢أ¢â‚¬ع‘ط¢آ¬ط£آ¢أ¢â€ڑآ¬أ¢â‚¬إ’ overlap-aware + no_show unlock
        if ($timeChanged) {
            $slotDuration = (int) Setting::get('appointment_slot_duration', 30);
            $newStart = Carbon::parse($data['date'] . ' ' . $data['time']);
            $newEnd = (clone $newStart)->addMinutes($slotDuration);

            $hasOverlap = Appointment::where('doctor_id', $data['doctor_id'])
                ->whereDate('date', $data['date'])
                ->where('id', '!=', $appointment->id)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->lockForUpdate()
                ->get()
                ->contains(function ($existing) use ($newStart, $newEnd) {
                    $existingStart = Carbon::parse($existing->date->format('Y-m-d') . ' ' . $existing->time->format('H:i:s'));
                    $existingEnd = (clone $existingStart)->addMinutes((int) Setting::get('appointment_slot_duration', 30));
                    return max($newStart->timestamp, $existingStart->timestamp) < min($newEnd->timestamp, $existingEnd->timestamp);
                });

            if ($hasOverlap) {
               throw new Exception(__('messages.timeSlotBooked'));
            }
        }

        if (isset($data['status']) && $data['status'] !== $appointment->status) {
            $appointment->assertLegalTransition($data['status']);
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

        $appointment->assertLegalTransition($status);

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

    /**
     * Re-open vitals for an appointment ط·آ£ط¢آ¢ط£آ¢أ¢â‚¬ع‘ط¢آ¬ط£آ¢أ¢â€ڑآ¬أ¢â‚¬إ’ routes through service layer.
     *
     * @param Appointment $appointment
     * @return bool
     */
    public function reopenVitals(Appointment $appointment): bool
    {
        $appointment->assertLegalTransition(Appointment::STATUS_PENDING);

        return $appointment->update([
            'vitals_unlocked' => true,
            'status' => Appointment::STATUS_PENDING,
        ]);
    }
}

