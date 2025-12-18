<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            [
                'name' => 'Dr. Ahmed Al-Qadhi',
                'specialty' => 'Cardiology',
                'phone' => '+967 711 111 111',
                'email' => 'ahmed.qadhi@clinic.com',
                'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'work_start_time' => '08:00',
                'work_end_time' => '16:00',
                'consultation_fee' => 5000,
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Fatima Al-Sharif',
                'specialty' => 'Dermatology',
                'phone' => '+967 711 222 222',
                'email' => 'fatima.sharif@clinic.com',
                'working_days' => ['Sunday', 'Monday', 'Wednesday', 'Thursday'],
                'work_start_time' => '09:00',
                'work_end_time' => '17:00',
                'consultation_fee' => 4000,
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Mohammed Al-Hamdani',
                'specialty' => 'Pediatrics',
                'phone' => '+967 711 333 333',
                'email' => 'mohammed.hamdani@clinic.com',
                'working_days' => ['Sunday', 'Tuesday', 'Thursday'],
                'work_start_time' => '08:00',
                'work_end_time' => '14:00',
                'consultation_fee' => 3500,
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Aisha Al-Maqtari',
                'specialty' => 'Orthopedics',
                'phone' => '+967 711 444 444',
                'email' => 'aisha.maqtari@clinic.com',
                'working_days' => ['Monday', 'Wednesday', 'Thursday'],
                'work_start_time' => '10:00',
                'work_end_time' => '18:00',
                'consultation_fee' => 6000,
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Yusuf Al-Nahdi',
                'specialty' => 'Neurology',
                'phone' => '+967 711 555 555',
                'email' => 'yusuf.nahdi@clinic.com',
                'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday'],
                'work_start_time' => '08:00',
                'work_end_time' => '15:00',
                'consultation_fee' => 7000,
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Mariam Al-Zubaydi',
                'specialty' => 'General Practice',
                'phone' => '+967 711 666 666',
                'email' => 'mariam.zubaydi@clinic.com',
                'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'work_start_time' => '08:00',
                'work_end_time' => '20:00',
                'consultation_fee' => 2500,
                'is_active' => true,
            ],
        ];

        foreach ($doctors as $doctor) {
            Doctor::create($doctor);
        }
    }
}
