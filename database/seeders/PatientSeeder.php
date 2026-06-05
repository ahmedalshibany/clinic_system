<?php

namespace Database\Seeders;

use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $staticPatients = [
            [
                'name' => 'Ahmed Hassan', 'age' => 35, 'gender' => 'male',
                'phone' => '+967 777 123 456', 'address' => 'Sana\'a, Yemen',
                'city' => 'Sana\'a', 'blood_type' => 'O+',
                'nationality' => 'Yemeni', 'marital_status' => 'married',
                'email' => 'ahmed.hassan@email.com',
                'date_of_birth' => Carbon::now()->subYears(35)->format('Y-m-d'),
                'status' => 'active',
            ],
            [
                'name' => 'Fatima Ali', 'age' => 28, 'gender' => 'female',
                'phone' => '+967 777 234 567', 'address' => 'Aden, Yemen',
                'city' => 'Aden', 'blood_type' => 'A+',
                'nationality' => 'Yemeni', 'marital_status' => 'married',
                'email' => 'fatima.ali@email.com',
                'date_of_birth' => Carbon::now()->subYears(28)->format('Y-m-d'),
                'status' => 'active',
            ],
        ];

        foreach ($staticPatients as $data) {
            Patient::create($data);
        }

        $firstNames = ['Mohammed', 'Ali', 'Omar', 'Hassan', 'Khalid', 'Abdullah', 'Ibrahim', 'Saeed', 'Nasser', 'Tariq', 'Yahya', 'Hussein', 'Saleh', 'Jamal', 'Fahd', 'Aisha', 'Mariam', 'Khadija', 'Noor', 'Huda', 'Layla', 'Sara', 'Rania', 'Amira', 'Nadia', 'Samira', 'Duaa', 'Rasha', 'Iman', 'Hind'];
        $lastNames = ['Al-Harbi', 'Al-Qahtani', 'Al-Otaibi', 'Al-Ghamdi', 'Al-Zahrani', 'Al-Shammari', 'Al-Maliki', 'Al-Anzi', 'Al-Dossary', 'Al-Subaie'];
        $genders = ['male', 'female'];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $cities = ['Sana\'a', 'Aden', 'Taiz', 'Hodeidah', 'Ibb', 'Mukalla', 'Dhamar', 'Saada', 'Marib', 'Al-Bayda'];
        $maritalStatuses = ['single', 'married', 'divorced', 'widowed'];
        $nationalities = ['Yemeni', 'Saudi', 'Omani', 'Egyptian', 'Jordanian', 'Syrian', 'Sudanese', 'Somali'];
        $chronicOptions = [null, 'Diabetes Type 2', 'Hypertension', 'Asthma', null, null, null];
        $allergyOptions = [null, null, null, 'Penicillin', null, 'Aspirin', null, null, null, null, null];

        for ($i = 0; $i < 100; $i++) {
            $gender = $genders[array_rand($genders)];
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $city = $cities[array_rand($cities)];
            $maritalStatus = $maritalStatuses[array_rand($maritalStatuses)];
            $nationality = $nationalities[array_rand($nationalities)];

            Patient::create([
                'name' => $firstName . ' ' . $lastName,
                'age' => rand(1, 85),
                'gender' => $gender,
                'phone' => '+967 7' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'address' => $city . ', Yemen',
                'city' => $city,
                'nationality' => $nationality,
                'marital_status' => $maritalStatus,
                'date_of_birth' => Carbon::now()->subYears(rand(1, 85))->subDays(rand(0, 364))->format('Y-m-d'),
                'blood_type' => $bloodTypes[array_rand($bloodTypes)],
                'email' => strtolower($firstName . '.' . $lastName . rand(1, 999) . '@email.com'),
                'chronic_diseases' => $chronicOptions[array_rand($chronicOptions)],
                'allergies' => $allergyOptions[array_rand($allergyOptions)],
                'status' => 'active',
            ]);
        }
    }
}
