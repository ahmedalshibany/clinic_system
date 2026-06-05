<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        Doctor::all()->each(function (Doctor $doctor) use ($dayNames) {
            $days = $doctor->working_days ?? ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

            foreach ($days as $day) {
                $dayNum = array_search($day, $dayNames);
                if ($dayNum === false) continue;

                $start = $doctor->work_start_time ?? '08:00';
                $end = $doctor->work_end_time ?? '16:00';
                $startMin = Carbon::parse($start)->hour * 60 + Carbon::parse($start)->minute;
                $endMin = Carbon::parse($end)->hour * 60 + Carbon::parse($end)->minute;
                $duration = 30;
                $maxSlots = (int) (($endMin - $startMin) / $duration);
                $maxSlots = max(1, $maxSlots);

                DoctorSchedule::firstOrCreate([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $dayNum,
                ], [
                    'start_time' => $start,
                    'end_time' => $end,
                    'slot_duration' => $duration,
                    'max_appointments' => min($maxSlots, 20),
                    'is_active' => true,
                ]);
            }
        });
    }
}
