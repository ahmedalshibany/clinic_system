<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vital extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'created_by',
        'temperature',
        'bp_systolic',
        'bp_diastolic',
        'pulse',
        'respiratory_rate',
        'weight',
        'height',
        'oxygen_saturation',
        'notes',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    /**
     * Get the appointment that owns the vitals.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the user who created the vitals (Nurse/Doctor).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get a formatted blood pressure string (e.g., "120/80").
     */
    public function getBloodPressureAttribute(): string
    {
        return "{$this->bp_systolic}/{$this->bp_diastolic}";
    }

    /**
     * Compute BMI from weight (kg) and height (cm).
     */
    public function getBmiAttribute(): ?float
    {
        if (!$this->height || (float)$this->height == 0) {
            return null;
        }
        $heightInMeters = (float)$this->height / 100;
        return round((float)$this->weight / ($heightInMeters * $heightInMeters), 1);
    }

    /**
     * WHO BMI classification.
     */
    public function getBmiCategoryAttribute(): ?string
    {
        $bmi = $this->bmi;
        if ($bmi === null) return null;
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25)   return 'Normal';
        if ($bmi < 30)   return 'Overweight';
        return 'Obese';
    }
}
