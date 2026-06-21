<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Exceptions\InvalidTransitionException;

class Appointment extends Model
{
    use HasFactory;

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_WAITING = 'waiting';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    const ALLOWED_TRANSITIONS = [
        self::STATUS_SCHEDULED   => [self::STATUS_CONFIRMED, self::STATUS_WAITING, self::STATUS_CANCELLED, self::STATUS_NO_SHOW],
        self::STATUS_PENDING     => [self::STATUS_CHECKED_IN, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED   => [self::STATUS_CHECKED_IN, self::STATUS_WAITING, self::STATUS_CANCELLED, self::STATUS_NO_SHOW],
        self::STATUS_CHECKED_IN  => [self::STATUS_WAITING, self::STATUS_PENDING, self::STATUS_CANCELLED],
        self::STATUS_WAITING     => [self::STATUS_IN_PROGRESS, self::STATUS_PENDING, self::STATUS_CANCELLED],
        self::STATUS_IN_PROGRESS => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED   => [],
        self::STATUS_CANCELLED   => [],
        self::STATUS_NO_SHOW     => [],
    ];

    public function isLegalTransition(string $newStatus): bool
    {
        if (!isset(self::ALLOWED_TRANSITIONS[$this->status])) {
            return false;
        }
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status], true);
    }

    public function assertLegalTransition(string $newStatus): void
    {
        if (!$this->isLegalTransition($newStatus)) {
            throw new InvalidTransitionException(
                "Transition from '{$this->status}' to '{$newStatus}' is not allowed."
            );
        }
    }

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'date',
        'time',
        'type',
        'status',
        'reason',
        'checked_in_at',
        'started_at',
        'completed_at',
        'notes',
        'diagnosis',
        'prescription',
        'fee',
        'vitals_unlocked',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'fee' => 'decimal:2',
        'vitals_unlocked' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeToday($query)
    {
        return $query->where('date', now()->toDateString());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeNoShow($query)
    {
        return $query->where('status', 'no_show');
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
    }

    public function getIsPastAttribute(): bool
    {
        return $this->date->isPast();
    }

    public function getFormattedDateTimeAttribute(): string
    {
        return $this->date->format('M d, Y') . ' at ' . $this->time->format('h:i A');
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function vital()
    {
        return $this->hasOne(Vital::class);
    }
}
