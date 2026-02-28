<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorLeave;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DoctorLeaveSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $doctors = Doctor::all();
        if ($doctors->isEmpty()) return;

        foreach ($doctors->random(min(3, $doctors->count())) as $doctor) {
            $startDate = Carbon::now()->addDays($faker->numberBetween(1, 30));
            DoctorLeave::create([
                'doctor_id' => $doctor->id,
                'start_date' => $startDate,
                'end_date' => (clone $startDate)->addDays($faker->numberBetween(1, 5)),
                'reason' => $faker->sentence,
            ]);
        }
    }
}
