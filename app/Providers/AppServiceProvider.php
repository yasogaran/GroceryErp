<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\GRN;
use App\Models\SupplierPayment;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;
use App\Observers\SaleObserver;
use App\Observers\SaleReturnObserver;
use App\Observers\GrnObserver;
use App\Observers\SupplierPaymentObserver;
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
        // Register model observers for cache invalidation and accounting integration
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);

        // Accounting integration observers - automatically post transactions to journal entries
        Sale::observe(SaleObserver::class);
        SaleReturn::observe(SaleReturnObserver::class);
        GRN::observe(GrnObserver::class);
        SupplierPayment::observe(SupplierPaymentObserver::class);
    }
}
