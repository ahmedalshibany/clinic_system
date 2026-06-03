<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vital;

class VitalPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'nurse']);
    }

    public function view(User $user, Vital $vital): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'nurse']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'nurse']);
    }

    public function update(User $user, Vital $vital): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'nurse']);
    }

    public function delete(User $user, Vital $vital = null): bool
    {
        return $user->role === 'admin';
    }
}
