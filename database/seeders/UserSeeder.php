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
            ['name' => 'Administrator', 'username' => 'admin', 'role' => 'admin', 'password' => Hash::make('admin123'), 'is_active' => true],
            ['name' => 'Dr. Ahmed Al-Qadhi', 'username' => 'ahmed.qadhi', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Fatima Al-Sharif', 'username' => 'fatima.sharif', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Mohammed Al-Hamdani', 'username' => 'mohammed.hamdani', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Aisha Al-Maqtari', 'username' => 'aisha.maqtari', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Yusuf Al-Nahdi', 'username' => 'yusuf.nahdi', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Dr. Mariam Al-Zubaydi', 'username' => 'mariam.zubaydi', 'role' => 'doctor', 'password' => Hash::make('password123'), 'is_active' => true],
            ['name' => 'Sarah Johnson', 'username' => 'receptionist', 'role' => 'receptionist', 'password' => Hash::make('reception123'), 'is_active' => true],
            ['name' => 'Nurse Joy', 'username' => 'nurse_joy', 'role' => 'nurse', 'password' => Hash::make('password'), 'is_active' => true],
        ];

        foreach ($users as $data) {
            User::create($data);
        }
    }
}
