<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache duration constants (in seconds)
     */
    const PRODUCTS_CACHE_DURATION = 900; // 15 minutes
    const CATEGORIES_CACHE_DURATION = 3600; // 1 hour
    const SETTINGS_CACHE_FOREVER = true;
    const DASHBOARD_STATS_DURATION = 300; // 5 minutes
    const REPORTS_CACHE_DURATION = 600; // 10 minutes

    /**
     * Get all active products with caching.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveProducts()
    {
        return Cache::remember('products.active', self::PRODUCTS_CACHE_DURATION, function () {
            return Product::with(['category', 'packaging'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get product by SKU with caching.
     *
     * @param string $sku
     * @return Product|null
     */
    public function getProductBySku(string $sku)
    {
        return Cache::remember("product.sku.{$sku}", 1800, function () use ($sku) {
            return Product::with(['category', 'packaging'])
                ->where('sku', $sku)
                ->where('is_active', true)
                ->first();
        });
    }

    /**
     * Get product by barcode with caching.
     *
     * @param string $barcode
     * @return Product|null
     */
    public function getProductByBarcode(string $barcode)
    {
        return Cache::remember("product.barcode.{$barcode}", 1800, function () use ($barcode) {
            return Product::with(['category', 'packaging'])
                ->where('barcode', $barcode)
                ->where('is_active', true)
                ->first();
        });
    }

    /**
     * Get all categories tree with caching.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCategoriesTree()
    {
        return Cache::remember('categories.tree', self::CATEGORIES_CACHE_DURATION, function () {
            return Category::with('children')
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get all active categories with caching.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveCategories()
    {
        return Cache::remember('categories.active', self::CATEGORIES_CACHE_DURATION, function () {
            return Category::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get all settings with caching.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllSettings()
    {
        return Cache::rememberForever('settings.all', function () {
            return Setting::all()->pluck('value', 'key');
        });
    }

    /**
     * Get low stock products with caching.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLowStockProducts()
    {
        return Cache::remember('products.low_stock', 300, function () {
            return Product::with('category')
                ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
                ->where('is_active', true)
                ->orderBy('current_stock_quantity', 'asc')
                ->get();
        });
    }

    /**
     * Get today's sales statistics with caching.
     *
     * @return array
     */
    public function getTodayStats()
    {
        return Cache::remember('dashboard.today_stats', self::DASHBOARD_STATS_DURATION, function () {
            $today = now()->format('Y-m-d');

            return [
                'total_sales' => \App\Models\Sale::whereDate('sale_date', $today)->sum('total_amount'),
                'sales_count' => \App\Models\Sale::whereDate('sale_date', $today)->count(),
                'avg_sale' => \App\Models\Sale::whereDate('sale_date', $today)->avg('total_amount'),
                'low_stock_count' => Product::whereColumn('current_stock_quantity', '<=', 'reorder_level')
                    ->where('is_active', true)
                    ->count(),
            ];
        });
    }

    /**
     * Invalidate product-related caches.
     *
     * @param Product|null $product
     * @return void
     */
    public function invalidateProductCache(?Product $product = null)
    {
        Cache::forget('products.active');
        Cache::forget('products.low_stock');

        if ($product) {
            Cache::forget("product.sku.{$product->sku}");
            Cache::forget("product.barcode.{$product->barcode}");
        }

        // Also invalidate dashboard stats as they depend on product data
        Cache::forget('dashboard.today_stats');
    }

    /**
     * Invalidate category-related caches.
     *
     * @return void
     */
    public function invalidateCategoryCache()
    {
        Cache::forget('categories.tree');
        Cache::forget('categories.active');
        Cache::forget('products.active'); // Products cache includes category data
    }

    /**
     * Invalidate settings cache.
     *
     * @return void
     */
    public function invalidateSettingsCache()
    {
        Cache::forget('settings.all');

        // Also forget individual setting caches (from Setting model)
        $settings = Setting::all();
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }
    }

    /**
     * Invalidate dashboard statistics cache.
     *
     * @return void
     */
    public function invalidateDashboardCache()
    {
        Cache::forget('dashboard.today_stats');
    }

    /**
     * Invalidate all application caches.
     *
     * @return void
     */
    public function clearAllCache()
    {
        Cache::flush();
    }

    /**
     * Get cache statistics.
     *
     * @return array
     */
    public function getCacheStats(): array
    {
        return [
            'products_cached' => Cache::has('products.active'),
            'categories_cached' => Cache::has('categories.active'),
            'settings_cached' => Cache::has('settings.all'),
            'dashboard_cached' => Cache::has('dashboard.today_stats'),
            'low_stock_cached' => Cache::has('products.low_stock'),
        ];
    }

    /**
     * Warm up critical caches.
     *
     * @return void
     */
    public function warmUpCache()
    {
        $this->getActiveProducts();
        $this->getActiveCategories();
        $this->getCategoriesTree();
        $this->getAllSettings();
        $this->getTodayStats();
        $this->getLowStockProducts();
    }
}
