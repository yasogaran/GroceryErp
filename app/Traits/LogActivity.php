<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Boot the trait and register model event listeners
     */
    protected static function bootLogsActivity(): void
    {
        // Log when a model is created
        static::created(function ($model) {
            self::logActivity('created', $model);
        });

        // Log when a model is updated
        static::updated(function ($model) {
            self::logActivity('updated', $model);
        });

        // Log when a model is deleted
        static::deleted(function ($model) {
            self::logActivity('deleted', $model);
        });
    }

    /**
     * Log the activity to Laravel's log file
     *
     * @param string $action
     * @param object $model
     */
    protected static function logActivity(string $action, object $model): void
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System';
        $userId = $user ? $user->id : null;
        $modelName = class_basename($model);
        $modelId = $model->id ?? 'N/A';
        $ipAddress = request()->ip();
        $timestamp = now()->toDateTimeString();

        // Format: [User: {name}] {action} {model} #{id} at {timestamp}
        $message = "[User: {$userName}] {$action} {$modelName} #{$modelId} at {$timestamp}";

        // Log with additional context
        Log::info($message, [
            'user_id' => $userId,
            'user_name' => $userName,
            'action' => $action,
            'model_name' => $modelName,
            'model_id' => $modelId,
            'ip_address' => $ipAddress,
            'timestamp' => $timestamp,
        ]);
    }
}