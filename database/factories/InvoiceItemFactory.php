<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $price = rand(500, 5000);
        return [
            'invoice_id' => Invoice::inRandomOrder()->first()->id,
            'description' => fake()->sentence(3),
            'quantity' => 1,
            'unit_price' => $price,
            'total' => $price,
        ];
    }
}
