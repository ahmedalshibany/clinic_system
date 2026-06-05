<?php

namespace Database\Factories;

use App\Models\PrescriptionItem;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    public function definition(): array
    {
        $dosages = ['1 tablet', '5ml', '1 capsule', '1 sachet', '2 tablets', '1 spray'];
        $frequencies = ['Once daily', 'Twice daily', 'Every 8 hours', 'Every 6 hours', 'Once before bed'];
        $durations = ['3 days', '5 days', '1 week', '10 days', '2 weeks'];
        $instructions = ['After meals', 'Before meals', 'Before sleep', 'As needed', 'With plenty of water'];

        return [
            'prescription_id' => Prescription::inRandomOrder()->first()->id,
            'medication_name' => fake()->word(),
            'dosage' => $dosages[array_rand($dosages)],
            'frequency' => $frequencies[array_rand($frequencies)],
            'duration' => $durations[array_rand($durations)],
            'quantity' => rand(1, 3),
            'instructions' => $instructions[array_rand($instructions)],
        ];
    }
}
