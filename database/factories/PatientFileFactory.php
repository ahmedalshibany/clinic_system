<?php

namespace Database\Factories;

use App\Models\PatientFile;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFileFactory extends Factory
{
    protected $model = PatientFile::class;

    public function definition(): array
    {
        $categories = ['lab_result', 'xray', 'mri', 'prescription', 'report', 'other'];
        $extensions = ['pdf', 'jpg', 'png', 'docx', 'xlsx'];
        $category = $categories[array_rand($categories)];
        $ext = $extensions[array_rand($extensions)];
        $filename = 'file_' . uniqid() . '.' . $ext;

        return [
            'patient_id' => Patient::inRandomOrder()->first()->id,
            'uploaded_by' => User::inRandomOrder()->first()->id,
            'filename' => $filename,
            'original_name' => fake()->word() . '_' . fake()->date() . '.' . $ext,
            'file_path' => 'uploads/patient_files/' . $filename,
            'file_type' => $ext,
            'file_size' => rand(51200, 5242880),
            'category' => $category,
            'description' => fake()->sentence(),
        ];
    }
}
