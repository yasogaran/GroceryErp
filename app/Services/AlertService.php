<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Check all products for low stock and create alerts.
     *
     * @return array Statistics about alerts created
     */
    public function checkLowStock(): array
    {
        $stats = [
            'checked' => 0,
            'low_stock' => 0,
            'critical_stock' => 0,
            'alerts_created' => 0,
        ];

        // Get all active products with low stock alerts enabled
        $products = Product::where('is_active', true)
            ->where('enable_low_stock_alert', true)
            ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
            ->with('category')
            ->get();

        $stats['checked'] = Product::where('is_active', true)
            ->where('enable_low_stock_alert', true)
            ->count();

        foreach ($products as $product) {
            $isCritical = $product->current_stock_quantity == 0;

            if ($isCritical) {
                $stats['critical_stock']++;
            } else {
                $stats['low_stock']++;
            }

            // Check if alert already exists (created today)
            $existingAlert = Notification::where('type', $isCritical ? 'critical_stock' : 'low_stock')
                ->where('data->product_id', $product->id)
                ->whereDate('created_at', today())
                ->first();

            if (!$existingAlert) {
                $this->createLowStockAlert($product, $isCritical);
                $stats['alerts_created']++;
            }
        }

        Log::info('Low stock check completed', $stats);

        return $stats;
    }

    /**
     * Create a low stock alert notification.
     *
     * @param Product $product
     * @param bool $isCritical
     * @return Notification
     */
    public function createLowStockAlert(Product $product, bool $isCritical = false): Notification
    {
        $type = $isCritical ? 'critical_stock' : 'low_stock';
        $priority = $isCritical ? 'critical' : 'high';

        $title = $isCritical
            ? 'Out of Stock Alert'
            : 'Low Stock Alert';

        $message = $isCritical
            ? "{$product->name} is OUT OF STOCK!"
            : "{$product->name} is running low. Current stock: {$product->current_stock_quantity} {$product->base_unit}";

        if ($product->reorder_quantity > 0) {
            $message .= " | Suggested reorder: {$product->reorder_quantity} {$product->base_unit}";
        }

        // Create notification for admins and managers (user_id = null means global)
        $notification = Notification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'user_id' => null, // Global notification
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'current_stock' => $product->current_stock_quantity,
                'reorder_level' => $product->reorder_level,
                'reorder_quantity' => $product->reorder_quantity,
                'category' => $product->category?->name,
            ],
        ]);

        return $notification;
    }

    /**
     * Get unread notification count for a user.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->count();
    }

    /**
     * Get recent notifications for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentNotifications(int $userId, int $limit = 10)
    {
        return Notification::forUser($userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read.
     *
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::forUser($userId)
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param int $userId
     * @return int Number of notifications marked as read
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete old notifications (older than specified days).
     *
     * @param int $days
     * @return int Number of notifications deleted
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);

        return Notification::where('created_at', '<', $cutoffDate)
            ->where('is_read', true)
            ->delete();
    }

    /**
     * Get low stock products count.
     *
     * @return array
     */
    public function getLowStockStats(): array
    {
        return [
            'critical' => Product::where('is_active', true)
                ->where('current_stock_quantity', '=', 0)
                ->count(),
            'low' => Product::where('is_active', true)
                ->where('current_stock_quantity', '>', 0)
                ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
                ->count(),
            'total_alerts' => Notification::ofType('low_stock')
                ->orWhere('type', 'critical_stock')
                ->unread()
                ->count(),
        ];
    }
}
