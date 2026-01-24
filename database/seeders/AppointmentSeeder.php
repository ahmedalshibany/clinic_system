<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::pluck('id')->toArray();
        $doctors = Doctor::pluck('id')->toArray();
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
        $types = ['Consultation', 'Checkup', 'Follow-up', 'Emergency'];
        $times = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '14:00', '14:30', '15:00', '15:30', '16:00'];

        // 1. Past Appointments (Last 30 Days) - Mostly Completed/Cancelled
        for ($i = 0; $i < 60; $i++) {
            $date = Carbon::today()->subDays(rand(1, 30));
            $status = $this->getRandomStatus(true); // Past favors completed

            Appointment::create([
                'patient_id' => $patients[array_rand($patients)],
                'doctor_id' => $doctors[array_rand($doctors)],
                'date' => $date,
                'time' => $times[array_rand($times)],
                'type' => $types[array_rand($types)],
                'status' => $status,
                'fee' => rand(2000, 8000),
                'completed_at' => $status === 'completed' ? $date->copy()->setTime(rand(9, 16), 0) : null,
                'started_at' => $status === 'completed' ? $date->copy()->setTime(rand(9, 16), 0)->subMinutes(30) : null,
            ]);
        }

        // 2. Today's Appointments (Queue)
        for ($i = 0; $i < 10; $i++) {
            $status = $i < 3 ? 'completed' : ($i < 5 ? 'in_progress' : 'waiting');
            if ($i > 7) $status = 'confirmed'; // Not arrived yet

            Appointment::create([
                'patient_id' => $patients[array_rand($patients)],
                'doctor_id' => $doctors[array_rand($doctors)],
                'date' => Carbon::today(),
                'time' => $times[$i % count($times)],
                'type' => $types[array_rand($types)],
                'status' => $status,
                'fee' => rand(2000, 8000),
                'checked_in_at' => in_array($status, ['waiting', 'in_progress', 'completed']) ? Carbon::now()->subMinutes(rand(10, 120)) : null,
            ]);
        }

        // 3. Future Appointments (Next 14 Days) - Mostly confirmed/pending
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::today()->addDays(rand(1, 14));
            
            Appointment::create([
                'patient_id' => $patients[array_rand($patients)],
                'doctor_id' => $doctors[array_rand($doctors)],
                'date' => $date,
                'time' => $times[array_rand($times)],
                'type' => $types[array_rand($types)],
                'status' => rand(0, 10) > 8 ? 'pending' : 'confirmed',
                'fee' => rand(2000, 8000),
            ]);
        }
    }

    private function getRandomStatus($isPast)
    {
        if ($isPast) {
            $rand = rand(0, 10);
            if ($rand < 7) return 'completed';
            if ($rand < 9) return 'cancelled';
            return 'no_show';
        }
        return 'confirmed';
    }
}
