<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $users = User::all();
        
        if ($users->isEmpty()) return;

        foreach ($users->random(min(5, $users->count())) as $user) {
            Notification::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'type' => 'system_alert',
                'title' => 'System Update',
                'message' => $faker->sentence,
                'data' => json_encode(['version' => '1.0.0']),
                'read_at' => $faker->boolean(50) ? now() : null,
                'link' => '/dashboard',
            ]);
        }
    }
}
