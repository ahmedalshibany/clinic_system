<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'receptionist', 'nurse']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'receptionist', 'nurse']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function delete(User $user, Appointment $appointment = null): bool
    {
        return $user->role === 'admin';
    }

    public function checkIn(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'receptionist', 'nurse']);
    }

    public function startVisit(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }

    public function complete(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }

    public function markNoShow(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }
}
