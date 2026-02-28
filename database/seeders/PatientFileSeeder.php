<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PatientFileSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $patients = Patient::all();
        $users = User::all();

        if ($patients->isEmpty() || $users->isEmpty()) return;

        foreach ($patients->random(min(5, $patients->count())) as $patient) {
            PatientFile::create([
                'patient_id' => $patient->id,
                'uploaded_by' => $users->random()->id,
                'filename' => $faker->uuid . '.pdf',
                'original_name' => 'Lab_Results.pdf',
                'file_path' => 'patient_files/' . $patient->id . '/Lab_Results.pdf',
                'file_type' => 'pdf',
                'file_size' => $faker->numberBetween(1024, 1024000),
                'category' => $faker->randomElement(['lab_result', 'xray', 'mri', 'prescription', 'report', 'other']),
                'description' => $faker->sentence,
            ]);
        }
    }
}
