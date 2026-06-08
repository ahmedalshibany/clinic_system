<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        static $codeCounter = 1;
        return [
            'code' => 'SVC-' . str_pad($codeCounter++, 4, '0', STR_PAD_LEFT),
            'category' => fake()->randomElement(['consultation', 'procedure', 'lab', 'imaging', 'other']),
            'name' => fake()->randomElement(['General Checkup', 'Blood Test', 'X-Ray', 'Ultrasound', 'Dental Cleaning', 'Eye Exam']),
            'price' => fake()->randomFloat(2, 10, 500),
            'is_active' => true,
        ];
    }
}
