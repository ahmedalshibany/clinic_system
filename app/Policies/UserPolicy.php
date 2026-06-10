<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, User $target): bool
    {
        return $user->role === 'admin' || $user->id === $target->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, User $target): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        return $user->role === 'admin';
    }

    public function toggleActive(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        if ($target->role === 'admin' && $user->role !== 'admin') {
            return false;
        }

        return $user->role === 'admin';
    }

    public function resetPassword(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        return $user->role === 'admin';
    }
}
