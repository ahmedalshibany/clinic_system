<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = [
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
            [
                'name' => 'Mohammed Omar',
                'age' => 45,
                'gender' => 'male',
                'phone' => '+967 777 345 678',
                'address' => 'Taiz, Yemen',
                'blood_type' => 'B+',
            ],
            [
                'name' => 'Aisha Saleh',
                'age' => 22,
                'gender' => 'female',
                'phone' => '+967 777 456 789',
                'address' => 'Hodeidah, Yemen',
                'blood_type' => 'AB+',
            ],
            [
                'name' => 'Yusuf Ibrahim',
                'age' => 55,
                'gender' => 'male',
                'phone' => '+967 777 567 890',
                'address' => 'Ibb, Yemen',
                'blood_type' => 'O-',
            ],
            [
                'name' => 'Mariam Abdullah',
                'age' => 32,
                'gender' => 'female',
                'phone' => '+967 777 678 901',
                'address' => 'Mukalla, Yemen',
                'blood_type' => 'A-',
            ],
            [
                'name' => 'Khalid Nasser',
                'age' => 40,
                'gender' => 'male',
                'phone' => '+967 777 789 012',
                'address' => 'Dhamar, Yemen',
                'blood_type' => 'B-',
            ],
            [
                'name' => 'Noura Saeed',
                'age' => 19,
                'gender' => 'female',
                'phone' => '+967 777 890 123',
                'address' => 'Amran, Yemen',
                'blood_type' => 'O+',
            ],
            [
                'name' => 'Ali Mansour',
                'age' => 60,
                'gender' => 'male',
                'phone' => '+967 777 901 234',
                'address' => 'Hajjah, Yemen',
                'blood_type' => 'A+',
            ],
            [
                'name' => 'Sara Ahmed',
                'age' => 25,
                'gender' => 'female',
                'phone' => '+967 777 012 345',
                'address' => 'Sana\'a, Yemen',
                'blood_type' => 'B+',
            ],
        ];

        foreach ($patients as $patient) {
            Patient::create($patient);
        }
    }
}
