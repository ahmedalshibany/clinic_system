<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'patient_code',
        'name',
        'name_en',
        'nationality',
        'id_number',
        'age',
        'gender',
        'marital_status',
        'occupation',
        'phone',
        'phone_secondary',
        'address',
        'city',
        'email',
        'date_of_birth',
        'medical_history',
        'chronic_diseases',
        'current_medications',
        'previous_surgeries',
        'family_history',
        'allergies',
        'blood_type',
        'emergency_contact',
        'emergency_phone',
        'emergency_relation',
        'insurance_provider',
        'insurance_number',
        'insurance_expiry',
        'photo',
        'notes',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'insurance_expiry' => 'date',
        'age' => 'integer',
    ];

    /**
     * Boot method - auto-generate patient_code on create.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->patient_code)) {
                $patient->patient_code = self::generatePatientCode();
            }
        });
    }

    /**
     * Generate a unique patient code.
     * Format: PAT-{YEAR}-{4-digit-sequence}
     * Example: PAT-2026-0001
     */
    public static function generatePatientCode(): string
    {
        $year = date('Y');
        $prefix = "PAT-{$year}-";
        
        // Get the last patient code for this year
        $lastPatient = self::where('patient_code', 'like', "{$prefix}%")
            ->orderBy('patient_code', 'desc')
            ->first();
        
        if ($lastPatient) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastPatient->patient_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate age from date_of_birth.
     */
    public function getCalculatedAgeAttribute(): ?int
    {
        if ($this->date_of_birth) {
            return Carbon::parse($this->date_of_birth)->age;
        }
        return $this->attributes['age'] ?? null;
    }

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
     * Get all files uploaded for the patient.
     */
    public function files(): HasMany
    {
        return $this->hasMany(PatientFile::class);
    }

    /**
     * Get all medical records for the patient.
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Get the patient's display name with code.
     */
    public function getDisplayNameAttribute(): string
    {
        $code = $this->patient_code ?? 'ID: ' . $this->id;
        return $this->name . ' (' . $code . ')';
    }

    /**
     * Scope for active patients.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive patients.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
    /**
     * Get all medical records for the patient.
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }
}

