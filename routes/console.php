<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic backups daily at 2 AM
Schedule::command('backup:create --type=automatic')->dailyAt('02:00');

// Schedule backup cleanup weekly (every Sunday at 3 AM)
Schedule::command('backup:cleanup')->weeklyOn(0, '03:00');

// Schedule low stock check daily at 8 AM
Schedule::command('stock:check-low')->dailyAt('08:00');
