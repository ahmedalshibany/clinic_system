<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PatientSeeder::class,
            DoctorSeeder::class,
            SettingSeeder::class,
            ServiceSeeder::class,
            MedicineSeeder::class,
            AppointmentSeeder::class,
            MedicalSeeder::class,
            FinancialSeeder::class,
        ]);
    }
}
