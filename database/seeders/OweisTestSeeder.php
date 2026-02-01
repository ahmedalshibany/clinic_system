<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;

class OweisTestSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a Doctor exists
        $doctor = Doctor::first();
        if (!$doctor) {
            // Minimal doctor creation if none exists
            $doctor = Doctor::create([
                'name' => 'Dr. Emergency Default',
                'specialty' => 'General',
                'phone' => '0000000000',
                'email' => 'default@clinic.com',
                'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'work_start_time' => '08:00',
                'work_end_time' => '17:00',
                'consultation_fee' => 1000,
                'is_active' => true,
            ]);
        }

        // Create or Find Patient Oweis
        $patient = Patient::firstOrCreate(
            ['name' => 'Oweis'],
            [
                'phone' => '1234567890',
                'age' => 30,
                'gender' => 'male', 
                // Add extended fields as null by default if not strictly required
            ]
        );

        // Create CONFIRMED Appointment for Today
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'), // Current time
            'status' => 'confirmed',
            'type' => 'Consultation'
        ]);

        $this->command->info("OweisTestSeeder: Created Patient {$patient->name} (ID: {$patient->id}) with Appointment.");
    }
}
