<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group_name',
        'description',
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $groupName
     * @param string|null $description
     * @return self
     */
    public static function set(string $key, $value, string $groupName = 'general', ?string $description = null): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group_name' => $groupName,
                'description' => $description,
            ]
        );

        // Clear cache for this setting
        Cache::forget("setting_{$key}");

        return $setting;
    }

    /**
     * Get all settings grouped by group_name
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllGrouped()
    {
        return self::all()->groupBy('group_name');
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }
    }
}
