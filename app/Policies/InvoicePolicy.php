<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'receptionist', 'doctor']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, ['admin', 'receptionist', 'doctor']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->role === 'admin';
    }

    public function recordPayment(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }
}
