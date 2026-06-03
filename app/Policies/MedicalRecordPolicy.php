<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MedicalRecord;

class MedicalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }

    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }

    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }

    public function delete(User $user, MedicalRecord $medicalRecord = null): bool
    {
        return $user->role === 'admin';
    }
}
