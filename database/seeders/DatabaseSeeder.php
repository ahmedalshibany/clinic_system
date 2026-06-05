<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Tier 1: No dependencies
            UserSeeder::class,
            ServiceSeeder::class,
            MedicineSeeder::class,
            SettingSeeder::class,

            // Tier 2: Depends on Users
            PatientSeeder::class,
            DoctorSeeder::class,

            // Tier 3: Depends on Doctors
            DoctorScheduleSeeder::class,
            DoctorLeaveSeeder::class,

            // Tier 4: Depends on Patients + Doctors
            AppointmentSeeder::class,

            // Tier 5: Depends on completed Appointments + Users
            VitalSeeder::class,
            MedicalRecordSeeder::class,

            // Tier 6: Depends on Patients + Users
            PatientFileSeeder::class,

            // Tier 7: Depends on completed Appointments + Users + Services
            InvoiceSeeder::class,

            // Tier 8: Depends on Users
            NotificationSeeder::class,
        ]);
    }
}
