<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Settings\CompanyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::post('language/{locale}', [LanguageController::class, 'store'])->name('language');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('company', [CompanyController::class, 'edit'])->name('company');
        Route::post('company', [CompanyController::class, 'update']);
        Route::delete('company/logo', [CompanyController::class, 'deleteLogo'])->name('company.logo.delete');
    });

    Route::middleware(\App\Http\Middleware\EnsureUserHasCompany::class)->group(function () {
        Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');

        Route::resource('clients', ClientController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('invoices', InvoiceController::class);
        Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

        Route::resource('devis', DevisController::class)->parameters(['devis' => 'devis']);
        Route::patch('devis/{devis}/status', [DevisController::class, 'updateStatus'])->name('devis.status');
        Route::get('devis/{devis}/pdf', [DevisController::class, 'pdf'])->name('devis.pdf');
        Route::post('devis/{devis}/convert', [DevisController::class, 'convert'])->name('devis.convert');
    });
});

require __DIR__.'/auth.php';
