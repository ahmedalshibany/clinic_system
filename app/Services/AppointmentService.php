<?php

namespace App\Services;

use App\Jobs\DispatchNotification;
use App\Jobs\GenerateConsultationInvoice;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
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

    public function createAppointment(array $data): Appointment
    {
        $doctor = Doctor::findOrFail($data['doctor_id']);

        $isOnLeave = $doctor->leaves()
            ->whereDate('start_date', '<=', $data['date'])
            ->whereDate('end_date', '>=', $data['date'])
            ->exists();

        if ($isOnLeave) {
            throw new Exception(__('messages.doctorOnLeave'));
        }

        $dayOfWeek = Carbon::parse($data['date'])->dayOfWeek;
        $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

        if (!$schedule) {
            throw new Exception(__('messages.doctorUnavailable'));
        }

        $apptTime = Carbon::parse($data['date'] . ' ' . $data['time']);
        $startTime = Carbon::parse($data['date'] . ' ' . $schedule->start_time);
        $endTime = Carbon::parse($data['date'] . ' ' . $schedule->end_time);

        if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
            throw new Exception(__('messages.timeOutsideHours'));
        }

        $slotDuration = $schedule->slot_duration;
        $minutesSinceStart = $apptTime->diffInMinutes($startTime, false);
        if ($minutesSinceStart % $slotDuration !== 0) {
            throw new Exception(__('messages.slotMustBe15min'));
        }

        DB::beginTransaction();

        try {
            $overlapDuration = $slotDuration ?? (int) Setting::get('appointment_slot_duration', 30);
            $newStart = Carbon::parse($data['date'] . ' ' . $data['time']);
            $newEnd = (clone $newStart)->addMinutes($overlapDuration);

            $hasOverlap = Appointment::where('doctor_id', $data['doctor_id'])
                ->whereDate('date', $data['date'])
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->lockForUpdate()
                ->get()
                ->contains(function ($existing) use ($newStart, $newEnd, $overlapDuration) {
                    $existingStart = $existing->date->copy()->setTimeFrom($existing->time);
                    $existingEnd = (clone $existingStart)->addMinutes($overlapDuration);
                    return max($newStart->timestamp, $existingStart->timestamp) < min($newEnd->timestamp, $existingEnd->timestamp);
                });

            if ($hasOverlap) {
                throw new Exception(__('messages.timeSlotBooked'));
            }

            $data['status'] = 'pending';

            $appointment = Appointment::create($data);

            DB::commit();

            return $appointment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateAppointment(Appointment $appointment, array $data): bool
    {
        $doctor = Doctor::findOrFail($data['doctor_id']);

        $timeChanged = $data['date'] !== $appointment->date->format('Y-m-d') ||
                       $data['time'] !== $appointment->time->format('H:i') ||
                       $data['doctor_id'] != $appointment->doctor_id;

        if ($timeChanged) {
            $isOnLeave = $doctor->leaves()
                ->whereDate('start_date', '<=', $data['date'])
                ->whereDate('end_date', '>=', $data['date'])
                ->exists();

            if ($isOnLeave) {
                throw new Exception(__('messages.doctorOnLeave'));
            }

            $dayOfWeek = Carbon::parse($data['date'])->dayOfWeek;
            $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

            if (!$schedule) {
                throw new Exception(__('messages.doctorUnavailable'));
            }

            $apptTime = Carbon::parse($data['date'] . ' ' . $data['time']);
            $startTime = Carbon::parse($data['date'] . ' ' . $schedule->start_time);
            $endTime = Carbon::parse($data['date'] . ' ' . $schedule->end_time);

            if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
                throw new Exception(__('messages.timeOutsideHours'));
            }

            $slotDuration = $schedule->slot_duration;
            $minutesSinceStart = $apptTime->diffInMinutes($startTime, false);
            if ($minutesSinceStart % $slotDuration !== 0) {
                throw new Exception(__('messages.slotMustBe15min'));
            }
        }

        if ($timeChanged) {
            $overlapDuration = $slotDuration ?? (int) Setting::get('appointment_slot_duration', 30);
            $newStart = Carbon::parse($data['date'] . ' ' . $data['time']);
            $newEnd = (clone $newStart)->addMinutes($overlapDuration);

            $hasOverlap = Appointment::where('doctor_id', $data['doctor_id'])
                ->whereDate('date', $data['date'])
                ->where('id', '!=', $appointment->id)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->lockForUpdate()
                ->get()
                ->contains(function ($existing) use ($newStart, $newEnd, $overlapDuration) {
                    $existingStart = $existing->date->copy()->setTimeFrom($existing->time);
                    $existingEnd = (clone $existingStart)->addMinutes($overlapDuration);
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

    public function updateStatus($id, string $status, array $extraData = []): bool
    {
        $appointment = $id instanceof Appointment ? $id : Appointment::findOrFail($id);

        $statusesNeedingRelations = ['waiting', 'completed', 'in_progress'];
        if (in_array($status, $statusesNeedingRelations)) {
            $appointment->loadMissing(['patient:id,name', 'doctor:id,name']);
        }

        $appointment->assertLegalTransition($status);

        $updateData = ['status' => $status];

        switch ($status) {
            case 'paid':
                $updateData['paid_at'] = now();
                break;

            case 'checked_in':
                $updateData['checked_in_at'] = now();
                break;

            case 'waiting':
                $doctor = $appointment->doctor;
                if ($doctor && $doctor->user_id) {
                    DispatchNotification::dispatch(
                        'doctor',
                        $doctor,
                        'appointment',
                        __('messages.notification.title_patient_ready'),
                        __('messages.notification.message_patient_ready', ['name' => $appointment->patient->name]),
                        [
                            'appointment_id' => $appointment->id,
                            'name' => $appointment->patient->name,
                            'title_key' => 'notification.title_patient_ready',
                            'message_key' => 'notification.message_patient_ready',
                        ],
                        route('dashboard')
                    );
                }
                break;

            case 'in_progress':
                $updateData['started_at'] = now();
                break;

            case 'completed':
                $updateData['completed_at'] = now();
                if (isset($extraData['diagnosis'])) {
                    $updateData['diagnosis'] = $extraData['diagnosis'];
                }

                DispatchNotification::dispatch(
                    'receptionists',
                    null,
                    'system',
                    __('messages.notification.title_visit_completed'),
                    __('messages.notification.message_visit_completed', [
                        'patient' => $appointment->patient->name ?? __('messages.unknown'),
                        'doctor' => $appointment->doctor->name ?? __('messages.unknown'),
                    ]),
                    [
                        'appointment_id' => $appointment->id,
                        'name' => $appointment->patient->name ?? '',
                    ],
                    route('dashboard')
                );

                GenerateConsultationInvoice::dispatch($appointment);
                break;
        }

        return $appointment->update($updateData);
    }

    public function deleteAppointment($id): void
    {
        $appointment = $id instanceof Appointment ? $id : Appointment::findOrFail($id);
        $appointment->loadMissing(['patient:id,name', 'doctor:id,name']);

        try {
            DB::beginTransaction();

            if ($appointment->doctor && $appointment->doctor->user_id) {
                DispatchNotification::dispatch(
                    'doctor',
                    $appointment->doctor,
                    'system',
                    __('messages.notification.title_appointment_cancelled'),
                    __('messages.notification.message_appointment_cancelled', [
                        'name' => $appointment->patient->name,
                        'date' => $appointment->date->format('Y-m-d'),
                    ]),
                    [
                        'appointment_id' => $appointment->id,
                        'date' => $appointment->date,
                        'type' => 'cancellation',
                        'name' => $appointment->patient->name,
                        'title_key' => 'notification.title_appointment_cancelled',
                        'message_key' => 'notification.message_appointment_cancelled',
                    ]
                );
            }

            $appointment->vital()->delete();
            $appointment->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reopenVitals(Appointment $appointment): bool
    {
        return $appointment->update([
            'vitals_unlocked' => true,
        ]);
    }
}
