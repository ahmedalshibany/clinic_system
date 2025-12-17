<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'specialty',
        'phone',
        'email',
        'bio',
        'avatar',
        'working_days',
        'work_start_time',
        'work_end_time',
        'consultation_fee',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'working_days' => 'array',
        'work_start_time' => 'datetime:H:i',
        'work_end_time' => 'datetime:H:i',
        'consultation_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all appointments for the doctor.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get today's appointments for the doctor.
     */
    public function todayAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)
            ->where('date', now()->toDateString())
            ->orderBy('time');
    }

    /**
     * Get upcoming appointments for the doctor.
     */
    public function upcomingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)
            ->where('date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('date')
            ->orderBy('time');
    }

    /**
     * Scope to get only active doctors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the doctor's display name with specialty.
     */
    public function getDisplayNameAttribute(): string
    {
        return 'Dr. ' . $this->name . ' (' . $this->specialty . ')';
    }
}
