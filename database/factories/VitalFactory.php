<?php

namespace Database\Factories;

use App\Models\Vital;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VitalFactory extends Factory
{
    protected $model = Vital::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::inRandomOrder()->first()->id,
            'created_by' => User::inRandomOrder()->first()->id,
            'temperature' => rand(360, 390) / 10,
            'bp_systolic' => rand(100, 140),
            'bp_diastolic' => rand(60, 90),
            'pulse' => rand(60, 100),
            'respiratory_rate' => rand(12, 20),
            'weight' => rand(500, 1200) / 10,
            'height' => rand(1500, 1900) / 10,
            'oxygen_saturation' => rand(95, 100),
        ];
    }
}
