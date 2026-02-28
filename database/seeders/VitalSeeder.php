<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Vital;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VitalSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $appointments = Appointment::where('status', 'completed')->get();
        $users = User::whereIn('role', ['nurse', 'doctor', 'admin'])->get();
        
        if ($appointments->isEmpty() || $users->isEmpty()) return;

        foreach ($appointments->random(min(10, $appointments->count())) as $appointment) {
            Vital::create([
                'appointment_id' => $appointment->id,
                'created_by' => $users->random()->id,
                'temperature' => $faker->randomFloat(1, 36, 39),
                'bp_systolic' => $faker->numberBetween(100, 140),
                'bp_diastolic' => $faker->numberBetween(60, 90),
                'pulse' => $faker->numberBetween(60, 100),
                'respiratory_rate' => $faker->numberBetween(12, 20),
                'weight' => $faker->randomFloat(2, 50, 120),
                'height' => $faker->randomFloat(2, 150, 190),
                'oxygen_saturation' => $faker->numberBetween(95, 100),
                'notes' => $faker->optional()->sentence,
            ]);
        }
    }
}
