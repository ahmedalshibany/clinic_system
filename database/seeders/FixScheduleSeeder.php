<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\DoctorSchedule;

class FixScheduleSeeder extends Seeder
{
    public function run()
    {
        $doctor = Doctor::find(1);
        if ($doctor) {
            // Create schedules for all days (0-6)
            for ($i = 0; $i <= 6; $i++) {
                DoctorSchedule::updateOrCreate(
                    ['doctor_id' => $doctor->id, 'day_of_week' => $i],
                    [
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                        'slot_duration' => 30,
                        'is_active' => true
                    ]
                );
            }
            $this->command->info('Schedules created for Doctor 1');
        } else {
            $this->command->error('Doctor 1 not found');
        }
    }
}
