<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;

class GrandpaJoeSeeder extends Seeder
{
    public function run(): void
    {
        // Find a Doctor
        $doctor = Doctor::first();
        if (!$doctor) {
            $doctor = Doctor::create([
                'name' => 'Dr. House',
                'specialty' => 'Diagnostic Medicine',
                'phone' => '123123123',
                'email' => 'house@clinic.com',
                'is_active' => true,
            ]);
        }

        // Create Patient Grandpa Joe
        $patient = Patient::firstOrCreate(
            ['name' => 'Grandpa Joe'],
            [
                'phone' => '999888777',
                'age' => 80,
                'gender' => 'male',
                'nationality' => 'USA',
                'id_number' => 'GJOE123'
            ]
        );

        // Create TODAY's appointment (Status: Confirmed, ready for check-in)
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'status' => 'confirmed', 
            'type' => 'Consultation'
        ]);

        $this->command->info("Golden Master: Grandpa Joe is ready for Check-In.");
    }
}
