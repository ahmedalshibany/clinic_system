<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Doctor;

class NotificationService
{
    /**
     * Notify a specific user.
     *
     * @param User $user
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @param string|null $link
     * @return Notification|null
     */
    public function notifyUser(User $user, string $type, string $title, string $message, array $data = [], ?string $link = null)
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'link' => $link,
        ]);
    }

    /**
     * Notify all admins.
     *
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @param string|null $link
     * @return void
     */
    public function notifyAdmins(string $type, string $title, string $message, array $data = [], ?string $link = null)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $this->notifyUser($admin, $type, $title, $message, $data, $link);
        }
    }

    /**
     * Notify a doctor (resolving their User account via email).
     *
     * @param Doctor $doctor
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @param string|null $link
     * @return void
     */
    public function notifyDoctor(Doctor $doctor, string $type, string $title, string $message, array $data = [], ?string $link = null)
    {
        // Try to find a user account associated with the doctor's email
        $user = User::where('email', $doctor->email)->first();

        if ($user) {
            $this->notifyUser($user, $type, $title, $message, $data, $link);
        }
    }
}
