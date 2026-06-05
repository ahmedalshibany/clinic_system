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
        $doctors = Doctor::all();
        if ($doctors->isEmpty()) return;

        $leaves = [
            ['doctor_id' => $doctors->first()->id, 'start_date' => Carbon::now()->addDays(20), 'end_date' => Carbon::now()->addDays(22), 'reason' => 'Annual leave'],
        ];

        if ($doctors->count() >= 3) {
            $leaves[] = ['doctor_id' => $doctors->get(2)->id, 'start_date' => Carbon::now()->addDays(15), 'end_date' => Carbon::now()->addDays(17), 'reason' => 'Conference attendance'];
        }

        if ($doctors->count() >= 5) {
            $leaves[] = ['doctor_id' => $doctors->get(4)->id, 'start_date' => Carbon::now()->addDays(25), 'end_date' => Carbon::now()->addDays(28), 'reason' => 'Personal leave'];
        }

        foreach ($leaves as $data) {
            DoctorLeave::create($data);
        }
    }
}
