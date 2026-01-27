<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class NurseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure the 'nurse' role exists in the enum check (DB level).
        // Since migration handles the enum modification, we assume it's done.
        
        // 2. Create the Nurse User
        $user = User::firstOrCreate(
            ['username' => 'nurse_joy'],
            [
                'name' => 'Nurse Joy',
                // 'email' removed as column does not exist
                'password' => Hash::make('password'),
                'role' => 'nurse',
                'is_active' => true,
            ]
        );
        
        $this->command->info("Nurse user created: {$user->username} (Password: password)");
    }
}
