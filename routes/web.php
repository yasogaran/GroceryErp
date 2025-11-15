<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Users\UserManagement;
use App\Livewire\Settings\SettingsManagement;
use App\Livewire\ActivityLogs;
use App\Livewire\Auth\Login;
use App\Livewire\Categories\CategoryManagement;
use App\Livewire\Products\ProductManagement;
use App\Livewire\Suppliers\SupplierManagement;
use App\Livewire\Suppliers\SupplierForm;
use App\Livewire\Suppliers\SupplierLedger;
use App\Livewire\GRN\GRNList;
use App\Livewire\GRN\GRNForm;
use App\Livewire\GRN\GRNApproval;
use App\Livewire\Suppliers\Payments\RecordPayment;
use App\Livewire\Suppliers\Payments\PaymentHistory;
use Illuminate\Support\Facades\Auth;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Guest routes (login, register, etc.)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Auth action routes
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Admin routes
    Route::middleware(['check.role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', UserManagement::class)->name('users.index');


        Route::get('/settings', SettingsManagement::class)->name('settings');

        Route::get('/activity-logs', ActivityLogs::class)->name('activity-logs');

    });

    // Category routes (store_keeper, manager, admin)
    Route::middleware(['check.role:store_keeper,manager,admin'])->group(function () {
        Route::get('/categories', CategoryManagement::class)->name('categories.index');
    });

    // Product routes (store_keeper, manager, admin)
    Route::middleware(['check.role:store_keeper,manager,admin'])->group(function () {
        Route::get('/products', ProductManagement::class)->name('products.index');
    });

    // Supplier routes (manager, admin)
    Route::middleware(['check.role:manager,admin'])->prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', SupplierManagement::class)->name('index');
        Route::get('/create', SupplierForm::class)->name('create');
        Route::get('/{id}/edit', SupplierForm::class)->name('edit');
        Route::get('/{id}/ledger', SupplierLedger::class)->name('ledger');
    });

    // GRN routes (store_keeper, manager, admin)
    Route::middleware(['check.role:store_keeper,manager,admin'])->prefix('grn')->name('grn.')->group(function () {
        Route::get('/', GRNList::class)->name('index');
        Route::get('/create', GRNForm::class)->name('create');
        Route::get('/{id}/edit', GRNForm::class)->name('edit');
        Route::get('/{id}/view', GRNApproval::class)->name('view');
    });

    // Supplier Payment routes (manager, admin)
    Route::middleware(['check.role:manager,admin'])->prefix('suppliers/payments')->name('suppliers.payments.')->group(function () {
        Route::get('/', PaymentHistory::class)->name('index');
        Route::get('/create', RecordPayment::class)->name('create');
    });

    // Additional routes will be added here as we build more modules
    // POS routes (cashier, manager, admin)
    // Route::middleware(['check.role:cashier,manager,admin'])->prefix('pos')->name('pos.')->group(function () {
    //     Route::get('/', POSComponent::class)->name('index');
    // });

    // Inventory routes (store_keeper, manager, admin)
    // Route::middleware(['check.role:store_keeper,manager,admin'])->prefix('inventory')->name('inventory.')->group(function () {
    //     Route::get('/', InventoryIndex::class)->name('index');
    // });

    // And so on for other modules...
});
