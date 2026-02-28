<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorService
{
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
                'password' => Hash::make('password123'), // Default password, they should change on first login
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
                foreach ($data['working_days'] as $day) {
                    DoctorSchedule::create([
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                        'start_time' => $data['work_start_time'],
                        'end_time' => $data['work_end_time'],
                        'slot_duration' => 30, // Default 30 min slots
                        'max_appointments' => 20, // Default arbitrary max
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
            // 1. Update the Doctor Profile
            $data['is_active'] = $data['is_active'] ?? true;
            $data['working_days'] = $data['working_days'] ?? [];
            $doctor->update($data);

            // 2. Update the associated User account if it exists
            // Since we added user_id to Doctors in logic but the original Migration didn't expose it in our file view,
            // We'll search by phone/email or assume a relation.
            // *Correction*: We need to ensure Doctor model has user_id. Looking at models we don't have user_id listed in $fillable in Doctor.php.
            // Wait, I will use a generic query based on email matching to find the associated User if user_id isn't guaranteed.
            $user = User::where('email', $doctor->email)->orWhere('phone', $doctor->phone)->first();
            if ($user) {
                 $user->update([
                     'name' => $data['name'],
                     'email' => $data['email'] ?? $user->email,
                     'phone' => $data['phone'],
                     'is_active' => $data['is_active'],
                 ]);
            }

            // 3. Sync DoctorSchedules
            if (isset($data['working_days'])) {
                // Remove out-of-sync schedules
                DoctorSchedule::where('doctor_id', $doctor->id)
                    ->whereNotIn('day_of_week', $data['working_days'])
                    ->delete();

                // Add or update schedules for selected days
                if (!empty($data['work_start_time']) && !empty($data['work_end_time'])) {
                    foreach ($data['working_days'] as $day) {
                        DoctorSchedule::updateOrCreate(
                            [
                                'doctor_id' => $doctor->id,
                                'day_of_week' => $day,
                            ],
                            [
                                'start_time' => $data['work_start_time'],
                                'end_time' => $data['work_end_time'],
                                'slot_duration' => 30,
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
}
