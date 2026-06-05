<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
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

        return [
            'id' => Str::uuid(),
            'user_id' => User::inRandomOrder()->first()->id,
            'type' => 'system_alert',
            'title' => 'System Update',
            'message' => $messages[array_rand($messages)],
            'data' => ['version' => '1.0.0'],
        ];
    }
}
