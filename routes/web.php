<?php

use App\Http\Controllers\Agent\AgentController;
use App\Http\Controllers\AI\AiChatController;
use App\Http\Controllers\Analysis\AnalysisController;
use App\Http\Controllers\Bid\BidController;
use App\Http\Controllers\Business\BusinessController;
use App\Http\Controllers\Auction\AuctionController;
use App\Http\Controllers\Batch\BatchController;
use App\Http\Controllers\Buy\BuyController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Cooperative\CooperativeController;
use App\Http\Controllers\Country\CountryController;
use App\Http\Controllers\Currency\CurrencyController;
use App\Http\Controllers\Documentation\DocumentationController;
use App\Http\Controllers\Farm\FarmController;
use App\Http\Controllers\Farm\GeocodeController;
use App\Http\Controllers\Forecast\ForecastController;
use App\Http\Controllers\Gallery\GalleryController;
use App\Http\Controllers\Harvest\HarvestController;
use App\Http\Controllers\Home\Dashboard as DashboardController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Farmer\FarmerController;
use App\Http\Controllers\Escrow\EscrowController;
use App\Http\Controllers\Input\AgriculturalInputController;
use App\Http\Controllers\Inspection\InspectionController;
use App\Http\Controllers\Lot\LotController;
use App\Http\Controllers\Market\MarketController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Origin\OriginController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Purchase\PurchaseController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\Season\SeasonController;
use App\Http\Controllers\Sell\SellController;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\Weather\WeatherForecastController;
use Illuminate\Support\Facades\Route;

// Public landing page.
Route::get('/', [HomeController::class, 'index'])->name('home');

// Design preview — a literal port of a Stitch mockup, kept isolated from
// the real app shell so it can be inspected without touching production
// pages. Not linked from anywhere; visit directly.
Route::get('/test/design', fn () => \Inertia\Inertia::render('Test/DesignPreview'))->name('test.design');
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

    // User profile routes. GET /user/profile intentionally reuses Jetstream's
    // own route name and URI — since routes/web.php is loaded after
    // Jetstream registers its routes, this same-URI/method registration
    // replaces Jetstream's vendor UserProfileController entirely, letting
    // our own controller decide (by role) which Vue page to render.
    Route::get('/user/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'store'])->name('profile.store');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/business', [ProfileController::class, 'updateBusiness'])->name('profile.business.update');
    Route::post('/profile/role', [ProfileController::class, 'updateRole'])->name('profile.role');
    Route::post('/profile/currency', [ProfileController::class, 'updateCurrency'])->name('profile.currency');



    // Farmer workspace routes.
    Route::prefix('farmer')->name('farmer.')->middleware('role:farmer,admin')->group(function () {
        Route::get('/', [FarmerController::class, 'index'])->name('index');
        Route::get('/create', [FarmerController::class, 'create'])->name('create');
        Route::post('/', [FarmerController::class, 'store'])->name('store');
        Route::get('/{farmer}', [FarmerController::class, 'show'])->name('show');
        Route::patch('/{farmer}', [FarmerController::class, 'update'])->name('update');
        Route::delete('/{farmer}', [FarmerController::class, 'destroy'])->name('destroy');
    });

    // Cooperative workspace routes.
    Route::prefix('cooperative')->name('cooperative.')->middleware('role:farmer,admin')->group(function () {
        Route::get('/', [CooperativeController::class, 'index'])->name('index');
        Route::get('/create', [CooperativeController::class, 'create'])->name('create');
        Route::post('/', [CooperativeController::class, 'store'])->name('store');
        Route::get('/{cooperative}', [CooperativeController::class, 'show'])->name('show');
    });

    // Agricultural input store — visible to every logged-in user; only
    // admins may add, edit, or remove inputs. Registered as its own
    // top-level group *before* the `farm` group below (which has a
    // `/{farm}` wildcard route) so that `/farm/inputs` resolves here
    // instead of being greedily captured as `{farm} = "inputs"`.
    Route::prefix('farm/inputs')->name('farm.inputs.')->group(function () {
        Route::get('/', [AgriculturalInputController::class, 'index'])->name('index');
        Route::post('/', [AgriculturalInputController::class, 'store'])->name('store');
        Route::patch('/{agriculturalInput}', [AgriculturalInputController::class, 'update'])->name('update');
        Route::delete('/{agriculturalInput}', [AgriculturalInputController::class, 'destroy'])->name('destroy');
    });

    // Farm workspace routes. Access is enforced by FarmPolicy: admins have
    // full access, everyone else must be subscribed to the Farmer Agent.
    Route::prefix('farm')->name('farm.')->group(function () {
        Route::get('/', [FarmController::class, 'index'])->name('index');
        Route::get('/farm-list', [FarmController::class, 'myFarms'])->name('mine');
        Route::get('/harvest', [HarvestController::class, 'mine'])->name('harvest.mine');
        Route::patch('/harvest/{harvest}', [HarvestController::class, 'update'])->name('harvest.update');
        Route::delete('/harvest/{harvest}', [HarvestController::class, 'destroy'])->name('harvest.destroy');
        Route::get('/weather', [WeatherForecastController::class, 'index'])->name('weather');
        Route::post('/', [FarmController::class, 'store'])->name('store');
        Route::get('/{farm}', [FarmController::class, 'show'])->name('show');
        Route::patch('/{farm}', [FarmController::class, 'update'])->name('update');
        Route::post('/{farm}/harvests', [FarmController::class, 'storeHarvest'])->name('harvests.store');
        Route::delete('/{farm}/harvests/{harvest}', [FarmController::class, 'destroyHarvest'])->name('harvests.destroy');
        Route::post('/{farm}/collections', [FarmController::class, 'storeCollection'])->name('collections.store');
        Route::patch('/{farm}/collections/{collection}', [FarmController::class, 'updateCollection'])->name('collections.update');
        Route::delete('/{farm}/collections/{collection}', [FarmController::class, 'destroyCollection'])->name('collections.destroy');
        Route::post('/{farm}/documents', [FarmController::class, 'storeDocument'])->name('documents.store');
        Route::delete('/{farm}/documents/{document}', [FarmController::class, 'destroyDocument'])->name('documents.destroy');
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

    // Auction workspace routes. Any authenticated user may browse and
    // watch the exchange; bidding itself is still gated separately by
    // the bid.* routes above (role:buyer,admin).
    Route::prefix('auction')->name('auction.')->group(function () {
        Route::get('/', [AuctionController::class, 'index'])->name('index');
    });

    // Checkout workspace routes. Any authenticated user may buy.
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/items', [CheckoutController::class, 'store'])->name('items.store');
        Route::patch('/items/{cartItem}', [CheckoutController::class, 'update'])->name('items.update');
        Route::delete('/items/{cartItem}', [CheckoutController::class, 'destroy'])->name('items.destroy');
        Route::get('/confirmation', [CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::post('/confirmation', [CheckoutController::class, 'placeOrder'])->name('placeOrder');
        Route::get('/order-confirmed', [CheckoutController::class, 'orderConfirmed'])->name('orderConfirmed');
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

    // Purchases — a simple, read-only receipt per checkout for the buyer's
    // own history. Separate from the request/offer Order workflow above.
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/{userOrder}', [PurchaseController::class, 'show'])->name('show');
        Route::patch('/{userOrder}/cancel', [PurchaseController::class, 'cancel'])->name('cancel');
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

    // Gallery — shared photo gallery visible to every logged-in user; only
    // admins may upload, edit, or delete images.
    Route::prefix('gallery')->name('gallery.')->group(function () {
        Route::get('/', [GalleryController::class, 'index'])->name('index');
        Route::post('/', [GalleryController::class, 'store'])->name('store');
        Route::patch('/{galleryImage}', [GalleryController::class, 'update'])->name('update');
        Route::delete('/{galleryImage}', [GalleryController::class, 'destroy'])->name('destroy');
    });

    // Business directory — every registered business profile, visible to
    // every logged-in user.
    Route::prefix('businesses')->name('businesses.')->group(function () {
        Route::get('/', [BusinessController::class, 'index'])->name('index');
    });

    // Business members workspace — a dedicated page for managing a
    // business's registered members, filled in directly by the business
    // (like a cooperative registering its member farmers) rather than by
    // invitation. Scoped to the caller's own business by default; admins
    // may target another business via `?business={id}`. Creating,
    // editing, and deleting members is restricted to the business's
    // owner or an admin — enforced in the controller.
    Route::prefix('business')->name('business.')->group(function () {
        Route::get('/members', [BusinessController::class, 'membersPage'])->name('members.index');
        Route::post('/members', [BusinessController::class, 'storeMember'])->name('members.store');
        Route::post('/members/import', [BusinessController::class, 'importMembers'])->name('members.import');
        Route::patch('/members/{businessMember}', [BusinessController::class, 'updateMember'])->name('members.update');
        Route::delete('/members/{businessMember}', [BusinessController::class, 'destroyMember'])->name('members.destroy');
    });

    // Store workspace — every user may register one store, which must be
    // verified by an admin before it can be opened (items added). Once
    // open, item status changes are logged automatically so every item
    // stays traceable.
    Route::prefix('store')->name('store.')->group(function () {
        Route::get('/', [StoreController::class, 'show'])->name('show');
        Route::get('/market', [StoreController::class, 'market'])->name('market');
        // Re-checks the caller's own account password, so it's throttled
        // the same way the real login form is.
        Route::post('/', [StoreController::class, 'save'])->middleware('throttle:6,1')->name('save');
        Route::post('/{store}/verify', [StoreController::class, 'verify'])->name('verify');
        Route::post('/{store}/reject', [StoreController::class, 'reject'])->name('reject');
        Route::post('/items', [StoreController::class, 'storeItem'])->name('items.store');
        Route::post('/items/import', [StoreController::class, 'importItems'])->name('items.import');
        Route::get('/items/{storeItem}', [StoreController::class, 'showItem'])->name('items.show');
        Route::patch('/items/{storeItem}', [StoreController::class, 'updateItem'])->name('items.update');
        Route::patch('/items/{storeItem}/status', [StoreController::class, 'updateItemStatus'])->name('items.status');
        Route::delete('/items/{storeItem}', [StoreController::class, 'destroyItem'])->name('items.destroy');
    });

    // Currencies — every logged-in user may browse them to set their own
    // settlement currency; only admins may create, edit, or delete them.
    Route::prefix('currencies')->name('currencies.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::post('/', [CurrencyController::class, 'store'])->name('store');
        Route::patch('/{currency}', [CurrencyController::class, 'update'])->name('update');
        Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('destroy');
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
        Route::get('/filter-options', [MarketController::class, 'filterOptions'])->name('filter.options');
        Route::get('/filter', [MarketController::class, 'filter'])->name('filter');
        Route::get('/{market}', [MarketController::class, 'show'])->name('show');
        Route::patch('/{market}', [MarketController::class, 'update'])->name('update');
        Route::delete('/{market}', [MarketController::class, 'destroy'])->name('destroy');
        Route::post('/{market}/images', [MarketController::class, 'storeImages'])->name('images.store');
        Route::delete('/{market}/images/{image}', [MarketController::class, 'destroyImage'])->name('images.destroy');
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
        Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/transfer', [WalletController::class, 'transfer'])->name('transfer');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
    });

    // Search — market items, plus a signed-in user's search history.
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/', [SearchController::class, 'index'])->name('index');
        Route::get('/suggest', [SearchController::class, 'suggest'])->name('suggest');
        Route::delete('/history', [SearchController::class, 'clearHistory'])->name('history.clear');
        Route::delete('/history/{log}', [SearchController::class, 'destroy'])->name('history.destroy');
    });

});
