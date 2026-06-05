<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $messages = [
            'New patient registration completed successfully.',
            'Appointment schedule updated for tomorrow.',
            'Lab results are ready for review.',
            'Prescription refill request received.',
            'Invoice payment confirmed.',
            'New doctor account created.',
            'System maintenance scheduled for tonight.',
            'Monthly report is now available.',
        ];

        foreach ($users->random(min(5, $users->count())) as $user) {
            Notification::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'type' => 'system_alert',
                'title' => 'System Update',
                'message' => $messages[array_rand($messages)],
                'data' => ['version' => '1.0.0'],
                'read_at' => rand(0, 1) ? now() : null,
                'link' => '/dashboard',
            ]);
        }
    }
}
