<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Http\Resources\ExchangeRateResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PriceIndexResource;
use App\Http\Resources\TaskResource;
use App\Models\CropGradeMetadata;
use App\Models\RoleMetadata;
use App\Services\AuctionService;
use App\Services\BusinessProfileService;
use App\Services\CalendarService;
use App\Services\ExchangeRateService;
use App\Services\MarketService;
use App\Services\OrderService;
use App\Services\PriceIndexService;
use App\Services\ProfileService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\LotRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Dashboard extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates,
        private readonly CalendarService $calendar,
        private readonly TaskService $tasks,
        private readonly OrderService $orders,
        private readonly ProfileService $profiles,
        private readonly BusinessProfileService $businessProfiles,
        private readonly MarketService $market,
        private readonly PriceIndexService $priceIndexes,
        private readonly AuctionService $auctions,
    ) {
    }

    /**
     * Legacy entry point — role-based routing was moved to dashboard().
     * Kept to avoid breaking any existing route references.
     */
    public function userDashboard()
    {
        // Role-specific routing is commented out; all roles now route through dashboard()
        // if ($role === 'buyer') {
        //     return self::buyerDashboard($request);
        // } elseif ($role === 'seller') {
        //     return self::sellerDashboard($request);
        // } elseif ($role === 'admin') {
        //     return self::adminDashboard($request);
        // } elseif ($role === 'investor') {
        //     return self::investorDashboard($request);
        // } elseif ($role === 'farmer') {
        //     return self::farmerDashboard($request);
        // } elseif ($role === 'exporter') {
        //     return self::exporterDashboard($request);
        // }

        return Inertia::render('Dashboard');
    }


    // ── Role-specific dashboards ─────────────────────────────────────────────
    // Each method loads the user profile, active roles, and determines whether
    // the role-selection modal should be shown on first load.

    /** Buyer-facing dashboard */
    static function buyerDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboards/BuyerDashboard', [
            'title'               => 'Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Seller-facing dashboard */
    static function sellerDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboard', [
            'title'               => 'Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Admin-facing dashboard */
    static function adminDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboards/AdminDashboard', [
            'title'               => 'Admin Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Investor-facing dashboard */
    static function investorDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboards/DashboardInvestor', [
            'title'               => 'Investor Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Farmer-facing dashboard */
    static function farmerDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboards/DashboardFarmer', [
            'title'               => 'Farmer Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Exporter-facing dashboard */
    static function exporterDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';


        return Inertia::render('Dashboards/DashboardExporter', [
            'title'               => 'Exporter Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }


    // ── Primary authenticated dashboard ─────────────────────────────────────

    /**
     * Default dashboard for all authenticated users.
     * Passes crop grades (for the Quick Buy modal grade dropdown).
     */
    public function dashboard(Request $request)
    {
        $user       = $request->user();
        $hasProfile = ! is_null($this->profiles->forUser($user->id))
            || ! is_null($this->businessProfiles->forUser($user->id));

        $cropGrades = CropGradeMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'name']);

        $lotRequest = LotRequest::query()
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();



        return Inertia::render('GeneralDashboard', [
            'hasProfile' => $hasProfile,
            'businessTypeOptions' => $this->businessProfiles->businessTypeOptions(),
            'cropGrades' => $cropGrades,
            'lotRequests' => $lotRequest,
            'exchangeRates' => ExchangeRateResource::collection($this->exchangeRates->all())->resolve(),
            'calendarEvents' => CalendarResource::collection($this->calendar->eventsForUser($user->id))->resolve(),
            'tasks' => TaskResource::collection($this->tasks->tasksForUser($user->id)->take(6))->resolve(),
            'orders' => OrderResource::collection($this->orders->ordersForUser($user->id)->take(6))->resolve(),
            'markets' => array_slice($this->market->marketPageListing(), 0, 5),
        ]);
    }

    // ── KPI drill-down pages ─────────────────────────────────────────────────
    // Each headline KPI on the general dashboard (Arabica, Robusta, Coffee C
    // Price, Market Sentiment) links here to let a user dig deeper into the
    // real listings and reference prices behind that number, at
    // /dashboard/{kpi}. Unknown slugs 404 rather than silently rendering
    // an empty page.

    /**
     * Render a KPI's drill-down page.
     */
    public function kpi(Request $request, string $kpi): Response
    {
        return match ($kpi) {
            'arabica' => $this->arabicaKpi(),
            'robusta' => $this->robustaKpi(),
            'coffee-c-price' => $this->coffeeCPriceKpi(),
            'market-sentiment' => $this->marketSentimentKpi(),
            default => throw new NotFoundHttpException(),
        };
    }

    /**
     * Every live Arabica listing, plus the admin-curated Uganda Arabica
     * reference price, if one exists.
     */
    private function arabicaKpi(): Response
    {
        $listings = $this->market->listingsByType('arabica');

        return Inertia::render('Dashboards/KpiArabica', [
            'listings' => $listings,
            'referencePrice' => $this->referencePriceFor('Arabica'),
            'stats' => $this->listingStats($listings),
        ]);
    }

    /**
     * Every live Robusta listing, plus the admin-curated Robusta
     * reference price, if one exists.
     */
    private function robustaKpi(): Response
    {
        $listings = $this->market->listingsByType('robusta');

        return Inertia::render('Dashboards/KpiRobusta', [
            'listings' => $listings,
            'referencePrice' => $this->referencePriceFor('Robusta'),
            'stats' => $this->listingStats($listings),
        ]);
    }

    /**
     * The full admin-curated price index — the closest real equivalent
     * this app has to a "Coffee C" benchmark price feed.
     */
    private function coffeeCPriceKpi(): Response
    {
        $indexes = $this->priceIndexes->all();

        return Inertia::render('Dashboards/KpiCoffeeCPrice', [
            'indexes' => PriceIndexResource::collection($indexes)->resolve(),
            'averagePrice' => $indexes->isNotEmpty() ? round((float) $indexes->avg('current_price'), 2) : null,
            'activeCount' => $indexes->where('status', 'active')->count(),
        ]);
    }

    /**
     * How live listings split across demand tiers, and the auction
     * exchange's own live bidding activity — real signals rolled into a
     * single "market sentiment" view.
     */
    private function marketSentimentKpi(): Response
    {
        return Inertia::render('Dashboards/KpiMarketSentiment', [
            'demand' => $this->market->demandBreakdown(),
            'auctionOverview' => $this->auctions->overview(request()->user()->id),
        ]);
    }

    /**
     * Look up a price index entry by its item name, shaped for display.
     */
    private function referencePriceFor(string $item): ?array
    {
        $index = $this->priceIndexes->forItem($item) ?? $this->priceIndexes->forItemLike($item);

        return $index ? PriceIndexResource::make($index)->resolve() : null;
    }

    /**
     * Real aggregate stats over a shaped listing array — count, average
     * price, and total volume.
     *
     * @param  array<int, array<string, mixed>>  $listings
     * @return array<string, mixed>
     */
    private function listingStats(array $listings): array
    {
        $count = count($listings);
        $prices = array_column($listings, 'price_per_kg');
        $quantities = array_column($listings, 'quantity');

        return [
            'count' => $count,
            'average_price_per_kg' => $count > 0 ? round(array_sum($prices) / $count, 2) : null,
            'total_quantity_kg' => round(array_sum($quantities), 2),
        ];
    }
}
