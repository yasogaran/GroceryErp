<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;
use App\Observers\SaleObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for cache invalidation
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        Sale::observe(SaleObserver::class);
    }
}
