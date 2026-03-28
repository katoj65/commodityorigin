<?php

use App\Http\Controllers\Farmer\FarmerController;
use App\Http\Controllers\Market\MarketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public landing page.
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Authenticated application routes.
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Main dashboard.
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Farmer workspace routes.
    Route::prefix('farmer')->name('farmer.')->group(function () {
        Route::get('/', [FarmerController::class, 'index'])->name('index');
        Route::get('/create', [FarmerController::class, 'create'])->name('create');
        Route::post('/', [FarmerController::class, 'store'])->name('store');
    });

    // Marketplace routes.
    Route::prefix('market')->name('market.')->group(function () {
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::get('/auction', [MarketController::class, 'auction'])->name('auction');
    });
});
