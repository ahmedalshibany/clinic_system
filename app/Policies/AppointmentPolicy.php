<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Doctor;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'receptionist', 'nurse']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if (in_array($user->role, ['admin', 'receptionist', 'nurse'])) {
            return true;
        }

        if ($user->isDoctor()) {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor && $appointment->doctor_id === $doctor->id;
        }

        return false;
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

    public function pay(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function startVisit(User $user, Appointment $appointment): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor && $appointment->doctor_id === $doctor->id;
        }
        return false;
    }

    public function complete(User $user, Appointment $appointment): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor && $appointment->doctor_id === $doctor->id;
        }
        return false;
    }

    public function markNoShow(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'receptionist', 'nurse']);
    }

    public function requestVitals(User $user, Appointment $appointment): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor && $appointment->doctor_id === $doctor->id;
        }
        return false;
    }

    public function directToRoom(User $user, Appointment $appointment): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor && $appointment->doctor_id === $doctor->id;
        }
        return false;
    }

    public function reopenVitals(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor']);
    }
}
