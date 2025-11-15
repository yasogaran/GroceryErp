<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Users\UserManagement;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'check.role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', UserManagement::class)->name('users.index');
});
