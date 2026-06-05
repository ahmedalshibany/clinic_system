<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = rand(2000, 10000);
        return [
            'patient_id' => Patient::inRandomOrder()->first()->id,
            'appointment_id' => Appointment::inRandomOrder()->first()->id,
            'created_by' => User::inRandomOrder()->first()->id,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'status' => 'sent',
        ];
    }
}
