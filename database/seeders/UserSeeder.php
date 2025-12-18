<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create doctor user
        User::create([
            'name' => 'Dr. John Smith',
            'username' => 'doctor',
            'password' => Hash::make('doctor123'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        // Create receptionist user
        User::create([
            'name' => 'Sarah Johnson',
            'username' => 'receptionist',
            'password' => Hash::make('reception123'),
            'role' => 'receptionist',
            'is_active' => true,
        ]);
    }
}
