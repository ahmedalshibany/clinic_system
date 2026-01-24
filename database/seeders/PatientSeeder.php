<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ar_SA'); // Use Arabic locale for names

        // Static Patients for specific testing
        $staticPatients = [
            [
                'name' => 'Ahmed Hassan',
                'age' => 35,
                'gender' => 'male',
                'phone' => '+967 777 123 456',
                'address' => 'Sana\'a, Yemen',
                'blood_type' => 'O+',
            ],
            [
                'name' => 'Fatima Ali',
                'age' => 28,
                'gender' => 'female',
                'phone' => '+967 777 234 567',
                'address' => 'Aden, Yemen',
                'blood_type' => 'A+',
            ],
        ];

        foreach ($staticPatients as $patient) {
            Patient::create($patient);
        }

        // Generate 50 Random Patients
        for ($i = 0; $i < 50; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            Patient::create([
                'name' => $faker->name($gender),
                'age' => $faker->numberBetween(1, 90),
                'gender' => $gender,
                'phone' => '+967 7' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . ' ' . $faker->numberBetween(100, 999),
                'address' => $faker->city . ', Yemen',
                'blood_type' => $faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
                'medical_history' => $faker->optional(0.3)->sentence,
            ]);
        }
    }
}
