<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $types = ['Consultation', 'Checkup', 'Follow-up', 'Emergency'];
        
        $appointments = [
            // Today's appointments
            [
                'patient_id' => 1,
                'doctor_id' => 1,
                'date' => Carbon::today(),
                'time' => '09:00',
                'type' => 'Consultation',
                'status' => 'confirmed',
            ],
            [
                'patient_id' => 2,
                'doctor_id' => 2,
                'date' => Carbon::today(),
                'time' => '10:00',
                'type' => 'Checkup',
                'status' => 'pending',
            ],
            [
                'patient_id' => 3,
                'doctor_id' => 3,
                'date' => Carbon::today(),
                'time' => '11:00',
                'type' => 'Follow-up',
                'status' => 'confirmed',
            ],
            [
                'patient_id' => 4,
                'doctor_id' => 4,
                'date' => Carbon::today(),
                'time' => '14:00',
                'type' => 'Consultation',
                'status' => 'pending',
            ],
            
            // Yesterday's appointments
            [
                'patient_id' => 5,
                'doctor_id' => 5,
                'date' => Carbon::yesterday(),
                'time' => '09:00',
                'type' => 'Consultation',
                'status' => 'completed',
            ],
            [
                'patient_id' => 6,
                'doctor_id' => 6,
                'date' => Carbon::yesterday(),
                'time' => '10:30',
                'type' => 'Emergency',
                'status' => 'completed',
            ],
            [
                'patient_id' => 7,
                'doctor_id' => 1,
                'date' => Carbon::yesterday(),
                'time' => '14:00',
                'type' => 'Follow-up',
                'status' => 'cancelled',
            ],
            
            // Tomorrow's appointments
            [
                'patient_id' => 8,
                'doctor_id' => 2,
                'date' => Carbon::tomorrow(),
                'time' => '09:00',
                'type' => 'Consultation',
                'status' => 'confirmed',
            ],
            [
                'patient_id' => 9,
                'doctor_id' => 3,
                'date' => Carbon::tomorrow(),
                'time' => '11:00',
                'type' => 'Checkup',
                'status' => 'pending',
            ],
            [
                'patient_id' => 10,
                'doctor_id' => 4,
                'date' => Carbon::tomorrow(),
                'time' => '15:00',
                'type' => 'Consultation',
                'status' => 'pending',
            ],
            
            // This week appointments
            [
                'patient_id' => 1,
                'doctor_id' => 5,
                'date' => Carbon::today()->addDays(2),
                'time' => '10:00',
                'type' => 'Follow-up',
                'status' => 'confirmed',
            ],
            [
                'patient_id' => 2,
                'doctor_id' => 6,
                'date' => Carbon::today()->addDays(3),
                'time' => '09:00',
                'type' => 'Checkup',
                'status' => 'pending',
            ],
            
            // Past appointments (completed)
            [
                'patient_id' => 3,
                'doctor_id' => 1,
                'date' => Carbon::today()->subDays(2),
                'time' => '09:00',
                'type' => 'Consultation',
                'status' => 'completed',
            ],
            [
                'patient_id' => 4,
                'doctor_id' => 2,
                'date' => Carbon::today()->subDays(3),
                'time' => '14:00',
                'type' => 'Emergency',
                'status' => 'completed',
            ],
            [
                'patient_id' => 5,
                'doctor_id' => 3,
                'date' => Carbon::today()->subDays(4),
                'time' => '11:00',
                'type' => 'Checkup',
                'status' => 'completed',
            ],
        ];

        foreach ($appointments as $appointment) {
            Appointment::create($appointment);
        }
    }
}
