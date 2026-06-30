<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAudit('created', $model, null, $model->toArray());
        });

        static::updated(function ($model) {
            $changed = $model->getDirty();
            $old = [];

            foreach ($changed as $key => $newValue) {
                if (in_array($key, $model->getHidden())) {
                    continue;
                }
                $old[$key] = $model->getOriginal($key);
            }

            if (!empty($old)) {
                static::logAudit('updated', $model, $old, $changed);
            }
        });

        static::deleted(function ($model) {
            static::logAudit('deleted', $model, $model->toArray(), null);
        });

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            static::restored(function ($model) {
                static::logAudit('restored', $model, null, $model->toArray());
            });
        }
    }

    protected static function logAudit(string $event, $model, ?array $oldValues, ?array $newValues): void
    {
        try {
            $user = Auth::user();

            AuditLog::create([
                'user_id' => $user?->id,
                'event' => $event,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'old_values' => $oldValues ?? [],
                'new_values' => $newValues ?? [],
                'ip_address' => Request::ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to write audit log', [
                'event' => $event,
                'model' => get_class($model),
                'model_id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
