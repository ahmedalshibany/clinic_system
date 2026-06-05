<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'username' => 'admin', 'email' => 'admin@clinic.com', 'phone' => '+967 700 000 001', 'role' => 'admin', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Ahmed Al-Qadhi', 'username' => 'ahmed.qadhi', 'email' => 'ahmed.qadhi@clinic.com', 'phone' => '+967 711 111 111', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Fatima Al-Sharif', 'username' => 'fatima.sharif', 'email' => 'fatima.sharif@clinic.com', 'phone' => '+967 711 222 222', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Mohammed Al-Hamdani', 'username' => 'mohammed.hamdani', 'email' => 'mohammed.hamdani@clinic.com', 'phone' => '+967 711 333 333', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Aisha Al-Maqtari', 'username' => 'aisha.maqtari', 'email' => 'aisha.maqtari@clinic.com', 'phone' => '+967 711 444 444', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Yusuf Al-Nahdi', 'username' => 'yusuf.nahdi', 'email' => 'yusuf.nahdi@clinic.com', 'phone' => '+967 711 555 555', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Nurse Amina', 'username' => 'nurse_amina', 'email' => 'nurse.amina@clinic.com', 'phone' => '+967 722 000 001', 'role' => 'nurse', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Nurse Layla', 'username' => 'nurse_layla', 'email' => 'nurse.layla@clinic.com', 'phone' => '+967 722 000 002', 'role' => 'nurse', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Sarah Johnson', 'username' => 'receptionist', 'email' => 'sarah.j@clinic.com', 'phone' => '+967 733 000 001', 'role' => 'receptionist', 'password' => Hash::make('password123'), 'is_active' => true],
        ];

        foreach ($users as $data) {
            User::create($data);
        }
    }
}
