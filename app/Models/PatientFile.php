<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'patient_id',
        'uploaded_by',
        'filename',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Category labels for display.
     */
    public const CATEGORY_LABELS = [
        'lab_result' => 'Lab Result',
        'xray' => 'X-Ray',
        'mri' => 'MRI',
        'prescription' => 'Prescription',
        'report' => 'Report',
        'other' => 'Other',
    ];

    /**
     * Get the patient that owns the file.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who uploaded the file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get formatted file size (KB, MB, etc.)
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get the category label for display.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Get the file extension icon class.
     */
    public function getIconClassAttribute(): string
    {
        return match ($this->file_type) {
            'pdf' => 'fas fa-file-pdf text-danger',
            'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-info',
            'doc', 'docx' => 'fas fa-file-word text-primary',
            'xls', 'xlsx' => 'fas fa-file-excel text-success',
            default => 'fas fa-file text-secondary',
        };
    }

    /**
     * Check if file is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return in_array($this->file_type, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Get the full URL to the file.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
