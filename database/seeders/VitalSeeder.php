<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Vital;
use Illuminate\Database\Seeder;

class VitalSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = Appointment::where('status', 'completed')->get();
        $users = User::pluck('id')->toArray();
        if ($appointments->isEmpty() || empty($users)) return;

        foreach ($appointments->random(min(80, $appointments->count())) as $appt) {
            Vital::create([
                'appointment_id' => $appt->id,
                'created_by' => $users[array_rand($users)],
                'temperature' => rand(360, 390) / 10,
                'bp_systolic' => rand(100, 140),
                'bp_diastolic' => rand(60, 90),
                'pulse' => rand(60, 100),
                'respiratory_rate' => rand(12, 20),
                'weight' => rand(500, 1200) / 10,
                'height' => rand(1500, 1900) / 10,
                'oxygen_saturation' => rand(95, 100),
            ]);
        }
    }
}
