<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group'];

    /**
     * Get setting value by key.
     */
    public static function get($key, $default = null)
    {
        return Cache::rememberForever("settings.{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? self::castValue($setting->value, $setting->type) : $default;
        });
    }

    /**
     * Set setting value by key.
     */
    public static function set($key, $value, $group = 'system')
    {
        $type = self::getType($value);
        $encodedValue = self::prepareValue($value, $type);

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encodedValue,
                'type' => $type,
                'group' => $group
            ]
        );

        Cache::forget("settings.{$key}");
        Cache::forget("settings_group.{$group}");
        
        return $setting;
    }

    /**
     * Get all settings from a specific group.
     */
    public static function getGroup($group)
    {
        return Cache::rememberForever("settings_group.{$group}", function () use ($group) {
            return self::where('group', $group)->get()->mapWithKeys(function ($item) {
                return [$item->key => self::castValue($item->value, $item->type)];
            });
        });
    }

    /**
     * Helper to cast value based on type.
     */
    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return (bool) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Helper to determine type from value.
     */
    protected static function getType($value)
    {
        if (is_int($value)) return 'integer';
        if (is_bool($value)) return 'boolean';
        if (is_array($value) || is_object($value)) return 'json';
        return 'string';
    }

    /**
     * Helper to prepare value for storage.
     */
    protected static function prepareValue($value, $type)
    {
        if ($type === 'boolean') {
            return $value ? '1' : '0';
        }
        if ($type === 'json') {
            return json_encode($value);
        }
        return (string) $value;
    }
}
