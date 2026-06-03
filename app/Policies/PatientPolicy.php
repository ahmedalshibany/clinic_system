<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'receptionist', 'nurse']);
    }

    public function view(User $user, Patient $patient): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'receptionist', 'nurse']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function update(User $user, Patient $patient): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function delete(User $user, Patient $patient = null): bool
    {
        return $user->role === 'admin';
    }
}
