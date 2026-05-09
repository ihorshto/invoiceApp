<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Settings\CompanyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('products', ProductController::class)->except(['show']);

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('company', [CompanyController::class, 'edit'])->name('company');
        Route::post('company', [CompanyController::class, 'update']);
        Route::delete('company/logo', [CompanyController::class, 'deleteLogo'])->name('company.logo.delete');
    });
});

require __DIR__.'/auth.php';
