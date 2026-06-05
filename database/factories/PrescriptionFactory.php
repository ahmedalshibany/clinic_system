<?php

namespace Database\Factories;

use App\Models\Prescription;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::inRandomOrder()->first()->id,
        ];
    }
}
