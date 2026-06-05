<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = [
            [
                'username' => 'ahmed.qadhi',
                'name' => 'Dr. Ahmed Al-Qadhi',
                'specialty' => 'Cardiology',
                'phone' => '+967 711 111 111',
                'email' => 'ahmed.qadhi@clinic.com',
                'bio' => 'Senior cardiologist with 15+ years of experience in interventional cardiology. Specializes in hypertension management and heart disease prevention.',
                'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'work_start_time' => '08:00',
                'work_end_time' => '16:00',
                'consultation_fee' => 5000.00,
                'is_active' => true,
            ],
            [
                'username' => 'fatima.sharif',
                'name' => 'Dr. Fatima Al-Sharif',
                'specialty' => 'Dermatology',
                'phone' => '+967 711 222 222',
                'email' => 'fatima.sharif@clinic.com',
                'bio' => 'Board-certified dermatologist. Expertise in medical and cosmetic dermatology, skin cancer screening, and pediatric dermatology.',
                'working_days' => ['Sunday', 'Monday', 'Wednesday', 'Thursday'],
                'work_start_time' => '09:00',
                'work_end_time' => '17:00',
                'consultation_fee' => 4000.00,
                'is_active' => true,
            ],
            [
                'username' => 'mohammed.hamdani',
                'name' => 'Dr. Mohammed Al-Hamdani',
                'specialty' => 'Pediatrics',
                'phone' => '+967 711 333 333',
                'email' => 'mohammed.hamdani@clinic.com',
                'bio' => 'Experienced pediatrician specialized in neonatal care and childhood developmental disorders. Compassionate care for children of all ages.',
                'working_days' => ['Sunday', 'Tuesday', 'Thursday'],
                'work_start_time' => '08:00',
                'work_end_time' => '14:00',
                'consultation_fee' => 3500.00,
                'is_active' => true,
            ],
            [
                'username' => 'aisha.maqtari',
                'name' => 'Dr. Aisha Al-Maqtari',
                'specialty' => 'Orthopedics',
                'phone' => '+967 711 444 444',
                'email' => 'aisha.maqtari@clinic.com',
                'bio' => 'Orthopedic surgeon specializing in sports medicine, joint replacements, and fracture management. Dedicated to restoring mobility.',
                'working_days' => ['Monday', 'Wednesday', 'Thursday'],
                'work_start_time' => '10:00',
                'work_end_time' => '18:00',
                'consultation_fee' => 6000.00,
                'is_active' => true,
            ],
            [
                'username' => 'yusuf.nahdi',
                'name' => 'Dr. Yusuf Al-Nahdi',
                'specialty' => 'Neurology',
                'phone' => '+967 711 555 555',
                'email' => 'yusuf.nahdi@clinic.com',
                'bio' => 'Consultant neurologist with expertise in stroke management, epilepsy, headache disorders, and neurodegenerative diseases.',
                'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday'],
                'work_start_time' => '08:00',
                'work_end_time' => '15:00',
                'consultation_fee' => 7000.00,
                'is_active' => true,
            ],
            [
                'username' => 'mariam.zubaydi',
                'name' => 'Dr. Mariam Al-Zubaydi',
                'specialty' => 'General Practice',
                'phone' => '+967 711 666 666',
                'email' => 'mariam.zubaydi@clinic.com',
                'bio' => 'Compassionate general practitioner with broad experience in family medicine, preventive care, and chronic disease management for all age groups.',
                'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'work_start_time' => '08:00',
                'work_end_time' => '20:00',
                'consultation_fee' => 2500.00,
                'is_active' => true,
            ],
        ];

        foreach ($doctors as $data) {
            $username = $data['username'];
            unset($data['username']);
            $user = User::where('username', $username)->first();
            if ($user) {
                $data['user_id'] = $user->id;
            }
            Doctor::create($data);
        }
    }
}
