<?php

use App\Http\Controllers\Analysis\AnalysisController;
use App\Http\Controllers\Bid\BidController;
use App\Http\Controllers\Auction\AuctionController;
use App\Http\Controllers\Batch\BatchController;
use App\Http\Controllers\Buy\BuyController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Cooperative\CooperativeController;
use App\Http\Controllers\Farm\FarmController;
use App\Http\Controllers\Harvest\HarvestController;
use App\Http\Controllers\Home\Dashboard as DashboardController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Farmer\FarmerController;
use App\Http\Controllers\Lot\LotController;
use App\Http\Controllers\Market\MarketController;
use App\Http\Controllers\Origin\OriginController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Season\SeasonController;
use App\Http\Controllers\Sell\SellController;
use Illuminate\Support\Facades\Route;

// Public landing page.
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/news', [MarketController::class, 'marketIntelligence'])->name('market.news');
Route::get('/live-market', [MarketController::class, 'liveMarket'])->name('market.live');
Route::get('/origins', [OriginController::class, 'index'])->name('origin.index');





// Authenticated application routes.
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Main dashboard.
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('dashboard');

    // User profile routes.
    Route::post('/profile', [ProfileController::class, 'store'])->name('profile.store');
    Route::post('/profile/role', [ProfileController::class, 'updateRole'])->name('profile.role');



    // Farmer workspace routes.
    Route::prefix('farmer')->name('farmer.')->group(function () {
        Route::get('/', [FarmerController::class, 'index'])->name('index');
        Route::get('/create', [FarmerController::class, 'create'])->name('create');
        Route::post('/', [FarmerController::class, 'store'])->name('store');
        Route::get('/{farmer}', [FarmerController::class, 'show'])->name('show');
    });

    // Cooperative workspace routes.
    Route::prefix('cooperative')->name('cooperative.')->group(function () {
        Route::get('/', [CooperativeController::class, 'index'])->name('index');
        Route::get('/create', [CooperativeController::class, 'create'])->name('create');
        Route::post('/', [CooperativeController::class, 'store'])->name('store');
        Route::get('/{cooperative}', [CooperativeController::class, 'show'])->name('show');
    });

    // Farm workspace routes.
    Route::prefix('farm')->name('farm.')->group(function () {
        Route::get('/', [FarmController::class, 'index'])->name('index');
        Route::get('/create/{farmer}', [FarmController::class, 'create'])->name('create');
        Route::post('/', [FarmController::class, 'store'])->name('store');
        Route::get('/{farm}', [FarmController::class, 'show'])->name('show');
    });

    // Lot workspace routes.
    Route::prefix('lot')->name('lot.')->group(function () {
        Route::get('/create', [LotController::class, 'create'])->name('create');
        Route::post('/', [LotController::class, 'store'])->name('store');
    });

    // Batch workspace routes.
    Route::prefix('batch')->name('batch.')->group(function () {
        Route::get('/', [BatchController::class, 'index'])->name('index');
        Route::get('/create', [BatchController::class, 'create'])->name('create');
        Route::get('/season/{season}/create', [SeasonController::class, 'createBatch'])->name('create-season');
        Route::get('/{batch}/create-lot', [LotController::class, 'createLot'])->name('create-lot');
        Route::post('/{batch}/create-lot', [LotController::class, 'storeFromBatch'])->name('store-lot');
        Route::post('/', [BatchController::class, 'store'])->name('store');
        Route::patch('/{batch}', [BatchController::class, 'update'])->name('update');
        Route::post('/{batch}/compliance', [BatchController::class, 'storeCompliance'])->name('compliance.store');
        Route::get('/{batch}', [BatchController::class, 'show'])->name('show');
    });

    // Harvest workspace routes.
    Route::prefix('harvest')->name('harvest.')->group(function () {
        Route::get('/', [HarvestController::class, 'index'])->name('index');
        Route::get('/create', [HarvestController::class, 'create'])->name('create');
        Route::post('/', [HarvestController::class, 'store'])->name('store');
        Route::post('/{harvest}/documents', [HarvestController::class, 'storeDocument'])->name('documents.store');
        Route::post('/{harvest}/sustainability', [HarvestController::class, 'storeHarvestSustainability'])->name('sustainability.store');
        Route::patch('/{harvest}', [HarvestController::class, 'update'])->name('update');
        Route::get('/{harvest}', [HarvestController::class, 'show'])->name('show');
    });

    // Season workspace routes.
    Route::prefix('season')->name('season.')->group(function () {
        Route::get('/', [SeasonController::class, 'index'])->name('index');
        Route::get('/create', [SeasonController::class, 'create'])->name('create');
        Route::post('/', [SeasonController::class, 'store'])->name('store');
        Route::get('/{season}/create-harvest', [SeasonController::class, 'createHarvest'])->name('create-harvest');
        Route::post('/{season}/create-harvest', [SeasonController::class, 'storeHarvest'])->name('store-harvest');
        Route::delete('/{season}/harvest/{harvest}', [SeasonController::class, 'destroyHarvest'])->name('harvest.destroy');
        Route::patch('/{season}', [SeasonController::class, 'update'])->name('update');
        Route::delete('/{season}', [SeasonController::class, 'destroy'])->name('destroy');
        Route::get('/{season}', [SeasonController::class, 'show'])->name('show');
    });

    // Bid workspace routes.
    Route::prefix('bid')->name('bid.')->group(function () {
        Route::get('/', [BidController::class, 'index'])->name('index');
    });

    // Auction workspace routes.
    Route::prefix('auction')->name('auction.')->group(function () {
        Route::get('/', [AuctionController::class, 'index'])->name('index');
    });

    // Checkout workspace routes.
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
    });

    // Marketplace routes.
    Route::prefix('market')->name('market.')->group(function () {
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::get('/auction', [MarketController::class, 'auction'])->name('auction');
    });

    // Buyer workspace routes.
    Route::prefix('buyer')->name('buyer.')->group(function () {
        Route::get('/', [BuyController::class, 'index'])->name('index');
    });

    // Seller workspace routes.
    Route::prefix('seller')->name('seller.')->group(function () {
        Route::get('/', [SellController::class, 'index'])->name('index');
    });

    // Analysis workspace routes.
    Route::prefix('analysis')->name('analysis.')->group(function () {
        Route::get('/', [AnalysisController::class, 'index'])->name('index');
    });

    });
