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
            NurseSeeder::class,
            PatientSeeder::class,
            DoctorSeeder::class,
            DoctorScheduleSeeder::class,
            DoctorLeaveSeeder::class,
            SettingSeeder::class,
            ServiceSeeder::class,
            MedicineSeeder::class,
            AppointmentSeeder::class,
            VitalSeeder::class,
            MedicalSeeder::class,
            FinancialSeeder::class,
            PatientFileSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
