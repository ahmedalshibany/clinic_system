<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorLeave;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DoctorLeaveSeeder extends Seeder
{
    public function run(): void
    {
        $leaves = [
            ['doctor_id' => 1, 'start_date' => Carbon::now()->addDays(20), 'end_date' => Carbon::now()->addDays(22), 'reason' => 'Annual leave'],
            ['doctor_id' => 3, 'start_date' => Carbon::now()->addDays(15), 'end_date' => Carbon::now()->addDays(17), 'reason' => 'Conference attendance'],
            ['doctor_id' => 5, 'start_date' => Carbon::now()->addDays(25), 'end_date' => Carbon::now()->addDays(28), 'reason' => 'Personal leave'],
        ];

        foreach ($leaves as $data) {
            DoctorLeave::create($data);
        }
    }
}
