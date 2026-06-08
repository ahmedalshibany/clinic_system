<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'specialty' => fake()->randomElement(['Cardiology', 'Dermatology', 'Neurology', 'Pediatrics', 'Orthopedics']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'consultation_fee' => fake()->randomFloat(2, 50, 500),
            'is_active' => true,
        ];
    }
}
