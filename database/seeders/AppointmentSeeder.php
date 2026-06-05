<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patientIds = Patient::pluck('id')->toArray();
        $doctorIds = Doctor::pluck('id')->toArray();
        $types = ['Consultation', 'Checkup', 'Follow-up', 'Emergency'];
        $times = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '14:00', '14:30', '15:00', '15:30', '16:00'];

        $statusWeights = ['completed' => 60, 'cancelled' => 15, 'no_show' => 10];

        for ($i = 0; $i < 350; $i++) {
            $date = Carbon::today()->subDays(rand(1, 180));
            if (in_array($date->dayOfWeek, [5, 6])) continue;
            $r = rand(0, 100);
            $status = 'completed';
            if ($r >= 60 && $r < 75) $status = 'cancelled';
            elseif ($r >= 75 && $r < 85) $status = 'no_show';

            $fee = $types[array_rand($types)] === 'Emergency' ? rand(5000, 12000) : rand(2000, 8000);

            $appt = Appointment::create([
                'patient_id' => $patientIds[array_rand($patientIds)],
                'doctor_id' => $doctorIds[array_rand($doctorIds)],
                'date' => $date,
                'time' => $times[array_rand($times)],
                'type' => $types[array_rand($types)],
                'status' => $status,
                'fee' => $fee,
                'completed_at' => $status === 'completed' ? $date->copy()->setTime(rand(9, 16), rand(0, 59)) : null,
                'started_at' => $status === 'completed' ? $date->copy()->setTime(rand(9, 16), rand(0, 59))->subMinutes(rand(15, 45)) : null,
            ]);

            if ($status === 'completed') {
                $appt->update(['vitals_unlocked' => (bool) rand(0, 1)]);
            }
        }

        $todayStatuses = ['completed', 'completed', 'completed', 'in_progress', 'in_progress', 'waiting', 'waiting', 'confirmed', 'confirmed', 'checked_in', 'checked_in', 'waiting', 'confirmed', 'in_progress', 'completed'];
        for ($i = 0; $i < 15; $i++) {
            $status = $todayStatuses[$i] ?? 'confirmed';
            $appt = Appointment::create([
                'patient_id' => $patientIds[array_rand($patientIds)],
                'doctor_id' => $doctorIds[array_rand($doctorIds)],
                'date' => Carbon::today(),
                'time' => $times[$i % count($times)],
                'type' => $types[array_rand($types)],
                'status' => $status,
                'fee' => rand(2000, 8000),
                'checked_in_at' => in_array($status, ['waiting', 'in_progress', 'checked_in', 'completed']) ? Carbon::now()->subMinutes(rand(10, 120)) : null,
                'completed_at' => $status === 'completed' ? Carbon::now()->subMinutes(rand(5, 60)) : null,
            ]);

            if ($status === 'completed') {
                $appt->update(['vitals_unlocked' => true]);
            }
        }

        for ($i = 0; $i < 100; $i++) {
            $date = Carbon::today()->addDays(rand(1, 14));
            if (in_array($date->dayOfWeek, [5, 6])) continue;
            $statusRand = rand(0, 10);
            $status = $statusRand > 8 ? 'pending' : 'confirmed';
            Appointment::create([
                'patient_id' => $patientIds[array_rand($patientIds)],
                'doctor_id' => $doctorIds[array_rand($doctorIds)],
                'date' => $date,
                'time' => $times[array_rand($times)],
                'type' => $types[array_rand($types)],
                'status' => $status,
                'fee' => rand(2000, 8000),
            ]);
        }
    }
}
