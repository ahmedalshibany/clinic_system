<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorService
{
    /**
     * Get paginated, filtered list of doctors.
     */
    public function getAllDoctors(array $filters): LengthAwarePaginator
    {
        $query = Doctor::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(8)->withQueryString();
    }

    /**
     * Create a new doctor along with their associated User account and default schedules.
     */
    public function createDoctor(array $data): Doctor
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the User account for the Doctor
            $username = strtolower(str_replace(' ', '.', $data['name'])) . rand(10, 99);
            $user = User::create([
                'name' => $data['name'],
                'username' => $username,
                'email' => $data['email'] ?? $username . '@clinic.local',
                'phone' => $data['phone'],
                'role' => 'doctor',
                'password' => Hash::make('password123'),
                'password_change_required' => true,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 2. Create the Doctor Profile
            $doctorData = array_merge($data, [
                'user_id' => $user->id,
                'is_active' => $data['is_active'] ?? true,
                'working_days' => $data['working_days'] ?? [],
            ]);
            $doctor = Doctor::create($doctorData);

            // 3. Create default DoctorSchedules based on working_days
            if (!empty($data['working_days']) && !empty($data['work_start_time']) && !empty($data['work_end_time'])) {
                $defaultSlotDuration = Setting::get('appointment_slot_duration', 30);
                foreach ($data['working_days'] as $day) {
                    DoctorSchedule::create([
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $this->mapDayNameToIndex($day),
                        'start_time' => $data['work_start_time'],
                        'end_time' => $data['work_end_time'],
                        'slot_duration' => $defaultSlotDuration,
                        'max_appointments' => 20,
                        'is_active' => true,
                    ]);
                }
            }

            return $doctor;
        });
    }

    /**
     * Update an existing doctor, their User account, and sync their schedules.
     */
    public function updateDoctor(Doctor $doctor, array $data): Doctor
    {
        return DB::transaction(function () use ($doctor, $data) {
            $data['is_active'] = $data['is_active'] ?? true;
            $data['working_days'] = $data['working_days'] ?? [];
            $doctor->update($data);

            if ($doctor->user) {
                $doctor->user->update([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? $doctor->user->email,
                    'phone' => $data['phone'],
                    'is_active' => $data['is_active'],
                ]);
            }

            if (isset($data['working_days'])) {
                $workingDayIndices = array_map([$this, 'mapDayNameToIndex'], $data['working_days']);
                DoctorSchedule::where('doctor_id', $doctor->id)
                    ->whereNotIn('day_of_week', $workingDayIndices)
                    ->delete();

                if (!empty($data['work_start_time']) && !empty($data['work_end_time'])) {
                    $defaultSlotDuration = Setting::get('appointment_slot_duration', 30);
                    foreach ($data['working_days'] as $day) {
                        DoctorSchedule::updateOrCreate(
                            [
                                'doctor_id' => $doctor->id,
                                'day_of_week' => $this->mapDayNameToIndex($day),
                            ],
                            [
                                'start_time' => $data['work_start_time'],
                                'end_time' => $data['work_end_time'],
                                'slot_duration' => $defaultSlotDuration,
                                'max_appointments' => 20,
                                'is_active' => true,
                            ]
                        );
                    }
                }
            }

            return $doctor;
        });
    }

    /**
     * Map a day name string to a Carbon day-of-week integer (0=Sunday, 6=Saturday).
     */
    protected function mapDayNameToIndex(string $day): int
    {
        $map = [
            'Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
            'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6,
        ];
        return $map[ucfirst(strtolower($day))] ?? (int) $day;
    }

    /**
     * Delete a doctor safely, checking for active appointments first.
     */
    public function deleteDoctor($id): void
    {
        $doctor = $id instanceof Doctor ? $id : Doctor::findOrFail($id);

        if ($doctor->appointments()->whereNotIn('status', ['cancelled', 'completed', 'no_show'])->exists()) {
            throw new Exception('Cannot delete doctor with active appointments.');
        }

        DB::transaction(function () use ($doctor) {
            DoctorSchedule::where('doctor_id', $doctor->id)->delete();
            $doctor->leaves()->delete();

            if ($doctor->user) {
                $doctor->user->delete();
            }

            $doctor->delete();
        });
    }
}
