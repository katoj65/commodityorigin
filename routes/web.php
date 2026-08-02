<?php

use App\Http\Controllers\Agent\AgentController;
use App\Http\Controllers\AI\AiChatController;
use App\Http\Controllers\Analysis\AnalysisController;
use App\Http\Controllers\Bid\BidController;
use App\Http\Controllers\Auction\AuctionController;
use App\Http\Controllers\Batch\BatchController;
use App\Http\Controllers\Buy\BuyController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Cooperative\CooperativeController;
use App\Http\Controllers\Country\CountryController;
use App\Http\Controllers\Documentation\DocumentationController;
use App\Http\Controllers\Farm\FarmController;
use App\Http\Controllers\Farm\GeocodeController;
use App\Http\Controllers\Forecast\ForecastController;
use App\Http\Controllers\Harvest\HarvestController;
use App\Http\Controllers\Home\Dashboard as DashboardController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Farmer\FarmerController;
use App\Http\Controllers\Escrow\EscrowController;
use App\Http\Controllers\Inspection\InspectionController;
use App\Http\Controllers\Lot\LotController;
use App\Http\Controllers\Market\MarketController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Origin\OriginController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\Season\SeasonController;
use App\Http\Controllers\Sell\SellController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\Weather\WeatherForecastController;
use Illuminate\Support\Facades\Route;

// Public landing page.
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/news', [MarketController::class, 'marketIntelligence'])->name('market.news');
Route::get('/live-market', [MarketController::class, 'liveMarket'])->name('market.live');
Route::get('/origins', [OriginController::class, 'index'])->name('origin.index');





// Authenticated application routes.
Route::middleware([
    'auth',
    config('jetstream.auth_session'),
])->group(function () {

    // Main dashboard.
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Apps (agents) directory.
    Route::prefix('apps')->name('apps.')->group(function () {
        Route::get('/', [AgentController::class, 'index'])->name('index');
        Route::post('/', [AgentController::class, 'store'])->name('store');
    });

    // Agent details, management (admin only), and subscriptions.
    Route::prefix('agent')->name('agent.')->group(function () {
        Route::get('/{agent}', [AgentController::class, 'show'])->name('show');
        Route::patch('/{agent}', [AgentController::class, 'update'])->name('update');
        Route::delete('/{agent}', [AgentController::class, 'destroy'])->name('destroy');
        Route::post('/{agent}/functions', [AgentController::class, 'storeFunction'])->name('functions.store');
        Route::patch('/{agent}/functions/{function}', [AgentController::class, 'updateFunction'])->name('functions.update');
        Route::delete('/{agent}/functions/{function}', [AgentController::class, 'destroyFunction'])->name('functions.destroy');
        Route::post('/{agent}/subscribe', [AgentController::class, 'userSubscription'])->name('subscribe');
        Route::delete('/{agent}/subscribe', [AgentController::class, 'unsubscribe'])->name('unsubscribe');
    });

    // AI chat.
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [AiChatController::class, 'index'])->name('index');
        Route::post('/', [AiChatController::class, 'store'])->name('store');
        Route::patch('/{chat}', [AiChatController::class, 'update'])->name('update');
        Route::delete('/{chat}', [AiChatController::class, 'destroy'])->name('destroy');
    });

    // Calendar.
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');
        Route::post('/', [CalendarController::class, 'store'])->name('store');
        Route::patch('/{calendar}', [CalendarController::class, 'update'])->name('update');
        Route::delete('/{calendar}', [CalendarController::class, 'destroy'])->name('destroy');
    });

    // Tasks.
    Route::prefix('task')->name('task.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::patch('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
    });

    // Market forecast.
    Route::prefix('forecast')->name('forecast.')->group(function () {
        Route::get('/', [ForecastController::class, 'index'])->name('index');
        Route::post('/', [ForecastController::class, 'store'])->name('store');
        Route::patch('/{forecast}', [ForecastController::class, 'update'])->name('update');
        Route::delete('/{forecast}', [ForecastController::class, 'destroy'])->name('destroy');
    });

    // Countries directory.
    Route::prefix('country')->name('country.')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('index');
        Route::post('/', [CountryController::class, 'store'])->name('store');
        Route::patch('/{country}', [CountryController::class, 'update'])->name('update');
        Route::delete('/{country}', [CountryController::class, 'destroy'])->name('destroy');
    });

    // User profile routes.
    Route::post('/profile', [ProfileController::class, 'store'])->name('profile.store');
    Route::post('/profile/role', [ProfileController::class, 'updateRole'])->name('profile.role');



    // Farmer workspace routes.
    Route::prefix('farmer')->name('farmer.')->middleware('role:farmer,admin')->group(function () {
        Route::get('/', [FarmerController::class, 'index'])->name('index');
        Route::get('/create', [FarmerController::class, 'create'])->name('create');
        Route::post('/', [FarmerController::class, 'store'])->name('store');
        Route::get('/{farmer}', [FarmerController::class, 'show'])->name('show');
    });

    // Cooperative workspace routes.
    Route::prefix('cooperative')->name('cooperative.')->middleware('role:farmer,admin')->group(function () {
        Route::get('/', [CooperativeController::class, 'index'])->name('index');
        Route::get('/create', [CooperativeController::class, 'create'])->name('create');
        Route::post('/', [CooperativeController::class, 'store'])->name('store');
        Route::get('/{cooperative}', [CooperativeController::class, 'show'])->name('show');
    });

    // Farm workspace routes. Access is enforced by FarmPolicy: admins have
    // full access, everyone else must be subscribed to the Farmer Agent.
    Route::prefix('farm')->name('farm.')->group(function () {
        Route::get('/', [FarmController::class, 'index'])->name('index');
        Route::get('/create', [FarmController::class, 'create'])->name('create');
        Route::get('/farm-list', [FarmController::class, 'myFarms'])->name('mine');
        Route::get('/harvest', [HarvestController::class, 'mine'])->name('harvest.mine');
        Route::patch('/harvest/{harvest}', [HarvestController::class, 'update'])->name('harvest.update');
        Route::delete('/harvest/{harvest}', [HarvestController::class, 'destroy'])->name('harvest.destroy');
        Route::get('/weather', [WeatherForecastController::class, 'index'])->name('weather');
        Route::post('/', [FarmController::class, 'store'])->name('store');
        Route::get('/{farm}', [FarmController::class, 'show'])->name('show');
        Route::patch('/{farm}', [FarmController::class, 'update'])->name('update');
        Route::patch('/{farm}/specs', [FarmController::class, 'updateSpecs'])->name('specs.update');
        Route::patch('/{farm}/location', [FarmController::class, 'updateLocation'])->name('location.update');
        Route::post('/{farm}/harvests', [FarmController::class, 'storeHarvest'])->name('harvests.store');
        Route::delete('/{farm}/harvests/{harvest}', [FarmController::class, 'destroyHarvest'])->name('harvests.destroy');
        Route::post('/geocode', GeocodeController::class)->name('geocode');
        Route::delete('/{farm}', [FarmController::class, 'destroy'])->name('destroy');
    });

    // Lot workspace routes.
    Route::prefix('lot')->name('lot.')->middleware('role:farmer,admin,buyer')->group(function () {
        Route::get('/', [LotController::class, 'index'])->name('index');
        Route::get('/create', [LotController::class, 'create'])->name('create');
        Route::post('/', [LotController::class, 'store'])->name('store');
        Route::post('/request', [LotController::class, 'storeLotRequest'])->name('request.store')->middleware('role:farmer,admin,buyer');
        Route::get('/requests', [LotController::class, 'lotRequestIndex'])->name('requests.index')->middleware('role:farmer,admin,buyer');
        Route::get('/request/{lotRequest}', [LotController::class, 'showLotRequest'])->name('request.show')->middleware('role:farmer,admin,buyer');
        Route::put('/request/{lotRequest}', [LotController::class, 'updateLotRequest'])->name('request.update')->middleware('role:farmer,admin,buyer');
        Route::delete('/request/{lotRequest}', [LotController::class, 'destroyLotRequest'])->name('request.destroy')->middleware('role:farmer,admin,buyer');
        Route::get('/{lot}/traceability', [LotController::class, 'lotTraceability'])->name('traceability');
        Route::get('/{lot}', [LotController::class, 'show'])->name('show');
        Route::post('/{lot}/publish', [LotController::class, 'publish'])->name('publish');
    });

    // Batch workspace routes.
    Route::prefix('batch')->name('batch.')->middleware('role:farmer,admin')->group(function () {
        Route::get('/', [BatchController::class, 'index'])->name('index');
        Route::get('/create', [BatchController::class, 'create'])->name('create');
        Route::get('/season/{season}/create', [SeasonController::class, 'createBatch'])->name('create-season');
        Route::get('/{batch}/create-lot', [LotController::class, 'createLot'])->name('create-lot');
        Route::post('/{batch}/create-lot', [LotController::class, 'storeFromBatch'])->name('store-lot');
        Route::post('/', [BatchController::class, 'store'])->name('store');
        Route::patch('/{batch}', [BatchController::class, 'update'])->name('update');
        Route::delete('/{batch}', [BatchController::class, 'destroy'])->name('destroy');
        Route::post('/{batch}/compliance', [BatchController::class, 'storeCompliance'])->name('compliance.store');
        Route::get('/{batch}', [BatchController::class, 'show'])->name('show');
    });

    // Harvest workspace routes.
    Route::prefix('harvest')->name('harvest.')->middleware('role:farmer,admin')->group(function () {
        Route::get('/', [HarvestController::class, 'index'])->name('index');
        Route::get('/create', [HarvestController::class, 'create'])->name('create');
        Route::post('/', [HarvestController::class, 'store'])->name('store');
        Route::post('/{harvest}/documents', [HarvestController::class, 'storeDocument'])->name('documents.store');
        Route::post('/{harvest}/sustainability', [HarvestController::class, 'storeHarvestSustainability'])->name('sustainability.store');
        Route::patch('/{harvest}', [HarvestController::class, 'update'])->name('update');
        Route::get('/{harvest}', [HarvestController::class, 'show'])->name('show');
    });

    // Season workspace routes.
    Route::prefix('season')->name('season.')->middleware('role:farmer,admin')->group(function () {
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
    Route::prefix('bid')->name('bid.')->middleware('role:buyer,admin')->group(function () {
        Route::get('/', [BidController::class, 'index'])->name('index');
        Route::get('/confirmation/status', [BidController::class, 'bidStatus'])->name('status');
        Route::post('/store', [BidController::class, 'storeBid'])->name('store');
        Route::get('/{lot}', [BidController::class, 'placeBid'])->name('place');
    });

    // Auction workspace routes.
    Route::prefix('auction')->name('auction.')->middleware('role:buyer,admin')->group(function () {
        Route::get('/', [AuctionController::class, 'index'])->name('index');
    });

    // Checkout workspace routes.
    Route::prefix('checkout')->name('checkout.')->middleware('role:buyer,admin')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::get('/order/confirmation', [CheckoutController::class, 'orderConfirmation'])->name('confirmation');
    });

    // Orders — buyers post requests and sellers post offers open; the
    // other side expresses intent to fulfill them, and the creator
    // confirms exactly one intent to lock in their counterparty.
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/intents', [OrderController::class, 'storeIntent'])->name('intents.store');
        Route::post('/{order}/intents/{intent}/confirm', [OrderController::class, 'confirmIntent'])->name('intents.confirm');
        Route::delete('/{order}/intents/{intent}', [OrderController::class, 'destroyIntent'])->name('intents.destroy');
        Route::post('/{order}/inspections', [InspectionController::class, 'store'])->name('inspections.store');
        Route::post('/{order}/inspections/{inspection}/acknowledge', [InspectionController::class, 'acknowledge'])->name('inspections.acknowledge');
        Route::post('/{order}/inspections/{inspection}/complete', [InspectionController::class, 'complete'])->name('inspections.complete');
        Route::post('/{order}/withdraw', [OrderController::class, 'withdraw'])->name('withdraw');
        Route::post('/{order}/repost', [OrderController::class, 'repost'])->name('repost');
        Route::patch('/{order}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    });

    // Inspections — the shared list of coffee-quality inspections tied to
    // confirmed orders; requested by the seller, approved by the buyer.
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionController::class, 'index'])->name('index');
    });

    // Escrow — the ledger of held/released order funds. Accounts are
    // created and released automatically when an admin activates shipping
    // on an order — see OrderController::update().
    Route::prefix('escrow')->name('escrow.')->group(function () {
        Route::get('/', [EscrowController::class, 'index'])->name('index');
    });

    // Documentation — the shared knowledge base, visible to every logged-in
    // user; only the uploader may edit or delete their own document.
    Route::prefix('documentation')->name('documentation.')->group(function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('index');
        Route::post('/', [DocumentationController::class, 'store'])->name('store');
        Route::patch('/{documentation}', [DocumentationController::class, 'update'])->name('update');
        Route::delete('/{documentation}', [DocumentationController::class, 'destroy'])->name('destroy');
    });

    // Notifications — the in-app channel of the notification system.
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::patch('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Marketplace routes.
    Route::prefix('market')->name('market.')->group(function () {
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::get('/active', [MarketController::class, 'activeMarket'])->name('active');
        Route::get('/auction', [MarketController::class, 'auction'])->name('auction');
        Route::get('/analysis', [MarketController::class, 'analyseMarket'])->name('analysis');
        Route::get('/request', [MarketController::class, 'request'])->name('request');
        Route::get('/compare', [MarketController::class, 'compareCountries'])->name('compare');
        Route::get('/offer', [MarketController::class, 'offers'])->name('offer');
    });

    // Buyer workspace routes.
    Route::prefix('buyer')->name('buyer.')->middleware('role:buyer,admin')->group(function () {
        Route::get('/', [BuyController::class, 'index'])->name('index');
    });

    // Buy — coffee requests (buyers submit, sellers discover & respond).
    Route::prefix('buy')->name('buy.')->group(function () {
        Route::get('/', [BuyController::class, 'buyer'])->name('index');
        Route::post('/', [BuyController::class, 'store'])->name('store');
        Route::post('/{lotRequest}/respond', [BuyController::class, 'respond'])->name('respond');
    });

    // Seller workspace routes.
    Route::prefix('seller')->name('seller.')->middleware('role:farmer,admin')->group(function () {
        Route::get('/', [SellController::class, 'index'])->name('index');
    });

    Route::get('/sell-coffee', [SellController::class, 'sellCoffee'])->name('seller.sell-coffee');

    // Analysis workspace routes.
    Route::prefix('analysis')->name('analysis.')->middleware('role:investor,admin')->group(function () {
        Route::get('/', [AnalysisController::class, 'index'])->name('index');
    });

    // Wallet workspace routes.
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/transfer', [WalletController::class, 'transfer'])->name('transfer');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
    });

    // Search — market items, plus a signed-in user's search history.
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/', [SearchController::class, 'index'])->name('index');
        Route::delete('/history', [SearchController::class, 'clearHistory'])->name('history.clear');
        Route::delete('/history/{log}', [SearchController::class, 'destroy'])->name('history.destroy');
    });

});
