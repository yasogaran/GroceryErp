<?php

use App\Models\Setting;

if (!function_exists('settings')) {
    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function settings(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}
