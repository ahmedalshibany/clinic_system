<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Doctor;

class DoctorPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'receptionist', 'nurse']);
    }

    public function view(User $user, Doctor $doctor): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'receptionist', 'nurse']);
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Doctor $doctor = null): bool
    {
        return $user->role === 'admin';
    }

    public function reopenVitals(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }
}
