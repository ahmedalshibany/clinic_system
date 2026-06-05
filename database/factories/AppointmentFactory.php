<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $types = ['Consultation', 'Checkup', 'Follow-up', 'Emergency'];
        $times = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '14:00', '14:30', '15:00', '15:30', '16:00'];

        return [
            'patient_id' => Patient::inRandomOrder()->first()->id,
            'doctor_id' => Doctor::inRandomOrder()->first()->id,
            'date' => fake()->dateTimeBetween('-6 months', '+14 days')->format('Y-m-d'),
            'time' => $times[array_rand($times)],
            'type' => $types[array_rand($types)],
            'status' => 'scheduled',
            'fee' => rand(2000, 8000),
        ];
    }
}
