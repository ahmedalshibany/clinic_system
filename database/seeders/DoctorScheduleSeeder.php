<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::all();
        if ($doctors->isEmpty()) return;

        foreach ($doctors as $doctor) {
            $days = [0, 1, 2, 3, 4, 5, 6];
            shuffle($days);
            $selectedDays = array_slice($days, 0, 5);
            
            foreach ($selectedDays as $day) {
                DoctorSchedule::firstOrCreate([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                ], [
                    'start_time' => '08:00:00',
                    'end_time' => '16:00:00',
                    'slot_duration' => 30,
                    'max_appointments' => 16,
                    'is_active' => true,
                ]);
            }
        }
    }
}
