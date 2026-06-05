<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $methods = ['cash', 'card', 'bank_transfer', 'insurance'];

        return [
            'invoice_id' => Invoice::inRandomOrder()->first()->id,
            'amount' => rand(1000, 10000),
            'payment_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'payment_method' => $methods[array_rand($methods)],
            'received_by' => User::inRandomOrder()->first()->id,
        ];
    }
}
