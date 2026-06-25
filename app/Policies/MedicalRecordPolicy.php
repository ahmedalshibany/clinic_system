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
        if ($user->isAdmin()) return true;
        if ($user->isAdmin()) return true;
        if ($user->isDoctor() && $medicalRecord->doctor_id === $user->doctor->id) return true;
        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }

    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isDoctor() && $medicalRecord->doctor_id === $user->doctor->id) return true;
        return false;
    }

    public function delete(User $user, MedicalRecord $medicalRecord = null): bool
    {
        return $user->role === 'admin';
    }
}
