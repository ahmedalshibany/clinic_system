<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'role',
        'password',
        'is_active',
        'password_change_required',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'password_change_required' => 'boolean',
    ];

    /**
     * Check if the user is a nurse.
     *
     * @return bool
     */
    public function isNurse()
    {
        return $this->role === 'nurse';
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if the user is a receptionist.
     *
     * @return bool
     */
    public function isReceptionist()
    {
        return $this->role === 'receptionist';
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Get the doctor profile associated with the user.
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }
}
