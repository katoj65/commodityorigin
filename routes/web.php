<?php

use App\Http\Controllers\Market\MarketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('market')->name('market.')->group(function () {
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::get('/auction', [MarketController::class, 'auction'])->name('auction');
    });
});
