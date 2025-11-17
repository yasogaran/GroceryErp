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

if (!function_exists('format_currency')) {
    /**
     * Format amount with currency symbol
     *
     * @param float|int|string|null $amount
     * @param bool $showSymbol
     * @return string
     */
    function format_currency($amount, bool $showSymbol = true): string
    {
        // Handle null or empty values
        if ($amount === null || $amount === '') {
            $amount = 0;
        }

        // Convert to float
        $amount = (float) $amount;

        $symbol = settings('currency_symbol', 'Rs.');
        $position = settings('currency_position', 'before');
        $decimalPlaces = (int) settings('decimal_places', 2);
        $thousandSep = settings('thousand_separator', ',');
        $decimalSep = settings('decimal_separator', '.');

        $formatted = number_format($amount, $decimalPlaces, $decimalSep, $thousandSep);

        if (!$showSymbol) {
            return $formatted;
        }

        if ($position === 'after') {
            return $formatted . ' ' . $symbol;
        }

        // Default: before
        return $symbol . ' ' . $formatted;
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol from settings
     *
     * @return string
     */
    function currency_symbol(): string
    {
        return settings('currency_symbol', 'Rs.');
    }
}

if (!function_exists('currency_input')) {
    /**
     * Get currency configuration for input fields
     *
     * @return array
     */
    function currency_input(): array
    {
        return [
            'symbol' => settings('currency_symbol', 'Rs.'),
            'position' => settings('currency_position', 'before'),
            'decimal_places' => (int) settings('decimal_places', 2),
            'thousand_separator' => settings('thousand_separator', ','),
            'decimal_separator' => settings('decimal_separator', '.'),
        ];
    }
}
