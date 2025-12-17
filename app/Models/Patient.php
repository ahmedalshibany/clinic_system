<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'age',
        'gender',
        'phone',
        'address',
        'email',
        'date_of_birth',
        'medical_history',
        'allergies',
        'blood_type',
        'emergency_contact',
        'emergency_phone',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'age' => 'integer',
    ];

    /**
     * Get all appointments for the patient.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get upcoming appointments for the patient.
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
     * Get the patient's full information as an array for display.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (ID: ' . $this->id . ')';
    }
}
