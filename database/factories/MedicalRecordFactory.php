<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::inRandomOrder()->first()->id,
            'doctor_id' => Doctor::inRandomOrder()->first()->id,
            'appointment_id' => Appointment::inRandomOrder()->first()->id,
            'visit_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'chief_complaint' => fake()->sentence(),
            'diagnosis' => fake()->word(),
            'treatment_plan' => fake()->sentence(),
        ];
    }
}
