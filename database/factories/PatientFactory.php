<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $genders = ['male', 'female'];
        $gender = $genders[array_rand($genders)];
        $firstNames = ['Mohammed', 'Ali', 'Omar', 'Hassan', 'Khalid', 'Abdullah', 'Ibrahim', 'Saeed', 'Nasser', 'Tariq', 'Yahya', 'Hussein', 'Saleh', 'Jamal', 'Fahd', 'Aisha', 'Mariam', 'Khadija', 'Noor', 'Huda', 'Layla', 'Sara', 'Rania', 'Amira', 'Nadia', 'Samira', 'Duaa', 'Rasha', 'Iman', 'Hind'];
        $lastNames = ['Al-Harbi', 'Al-Qahtani', 'Al-Otaibi', 'Al-Ghamdi', 'Al-Zahrani', 'Al-Shammari', 'Al-Maliki', 'Al-Anzi', 'Al-Dossary', 'Al-Subaie'];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $cities = ['Sana\'a', 'Aden', 'Taiz', 'Hodeidah', 'Ibb', 'Mukalla', 'Dhamar', 'Saada', 'Marib', 'Al-Bayda'];
        $maritalStatuses = ['single', 'married', 'divorced', 'widowed'];
        $nationalities = ['Yemeni', 'Saudi', 'Omani', 'Egyptian', 'Jordanian', 'Syrian', 'Sudanese', 'Somali'];

        return [
            'name' => $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
            'age' => rand(1, 85),
            'gender' => $gender,
            'phone' => '+967 7' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
            'address' => $cities[array_rand($cities)] . ', Yemen',
            'city' => $cities[array_rand($cities)],
            'nationality' => $nationalities[array_rand($nationalities)],
            'blood_type' => $bloodTypes[array_rand($bloodTypes)],
            'marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
            'email' => fake()->unique()->safeEmail(),
            'date_of_birth' => fake()->dateTimeBetween('-85 years', '-1 year')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
