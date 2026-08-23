<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Http\Resources\ExchangeRateResource;
use App\Http\Resources\ForecastResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\MarketListingResource;
use App\Http\Resources\OrderResource;
use App\Models\Market;
use App\Models\MarketImage;
use App\Services\BuyService;
use App\Services\CalendarService;
use App\Services\CartService;
use App\Services\CountryService;
use App\Services\ExchangeRateService;
use App\Services\ForecastService;
use App\Services\MarketImageService;
use App\Services\MarketService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MarketController extends Controller
{
    public function __construct(
        private readonly MarketService $market,
        private readonly CalendarService $calendar,
        private readonly ExchangeRateService $exchangeRates,
        private readonly BuyService $buy,
        private readonly OrderService $orders,
        private readonly ForecastService $forecasts,
        private readonly CountryService $countries,
        private readonly CartService $cart,
        private readonly MarketImageService $marketImages,
    ) {
    }

    /**
     * Display the market directory.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Market/MarketListings', [
            'markets' => $this->market->marketPageListing(),
            'calendarEvents' => CalendarResource::collection(
                $this->calendar->eventsForUser($request->user()->id),
            )->resolve(),
            'exchangeRates' => ExchangeRateResource::collection($this->exchangeRates->all())->resolve(),
        ]);
    }

    /**
     * Display buyer coffee requests for the market to browse and respond to.
     */
    public function request(Request $request): Response
    {
        $userId = $request->user()->id;

        return Inertia::render('Market/MarketRequestPage', [
            'myRequests' => $this->buy->requestsForUser($userId),
            'orders' => OrderResource::collection(
                $this->orders->query()
                    ->with(['buyer', 'seller'])
                    ->where('type', 'request')
                    ->latest()
                    ->get(),
            )->resolve(),
        ]);
    }

    /**
     * Display seller coffee offers for the market to browse and respond to.
     */
    public function offers(): Response
    {
        return Inertia::render('Market/OfferPage', [
            'orders' => OrderResource::collection(
                $this->orders->query()
                    ->with(['buyer', 'seller'])
                    ->where('type', 'offer')
                    ->latest()
                    ->get(),
            )->resolve(),
        ]);
    }

    /**
     * Display the market intelligence briefing.
     */
    public function marketIntelligence(): Response
    {
        return Inertia::render('Market/MarketIntelligence');
    }

    /**
     * Display the live market terminal.
     */
    public function liveMarket(): Response
    {
        return Inertia::render('Market/LiveMarket', [
            'lots' => $this->market->liveMarketListing(),
        ]);
    }

    /**
     * Display the live auction board.
     */
    public function auction(): Response
    {
        return Inertia::render('Market/Auction');
    }

    /**
     * Display only the live listings matching the buyer's filter dialog
     * criteria — a dedicated results page reached from the market filter.
     */
    public function filter(Request $request): Response
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string'],
            'origin' => ['nullable', 'string'],
            'process' => ['nullable', 'string'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_quality' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return Inertia::render('Market/MarketFilterResults', [
            'markets' => MarketListingResource::collection($this->market->filteredListing($filters))->resolve(),
            'filters' => $filters,
        ]);
    }

    /**
     * The distinct coffee types, origins, and processes available for the
     * market filter dialog's dropdowns.
     */
    public function filterOptions(): JsonResponse
    {
        return response()->json($this->market->filterOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display a market listing's product profile.
     */
    public function show(Request $request, Market $market): Response
    {
        return Inertia::render('Market/ProductProfile', [
            'item' => $this->market->show($market),
            'cartQuantity' => $this->cart->quantityFor($request->user()->id, 'market', $market->id),
            'canManage' => Gate::allows('update', $market),
            'maxImages' => MarketImageService::MAX_IMAGES,
        ]);
    }

    /**
     * Update the specified resource in storage. Owner only.
     */
    public function update(Request $request, Market $market): RedirectResponse
    {
        Gate::authorize('update', $market);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'process' => ['nullable', 'string', 'max:255'],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $market->update($validated);

        return back();
    }

    /**
     * Remove the specified resource from storage — owner only. Gallery
     * photos cascade-delete at the database level; their files are removed
     * first so nothing orphans in storage.
     */
    public function destroy(Market $market): RedirectResponse
    {
        Gate::authorize('delete', $market);

        foreach ($market->images as $image) {
            $this->marketImages->delete($image);
        }

        $market->delete();

        return redirect()->route('market.index');
    }

    /**
     * Add up to MarketImageService::MAX_IMAGES gallery photos to a
     * listing — owner only. Extra files beyond the remaining slots are
     * silently dropped rather than erroring, so a seller can always just
     * select their whole folder without doing the math themselves.
     */
    public function storeImages(Request $request, Market $market): RedirectResponse
    {
        Gate::authorize('update', $market);

        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $this->marketImages->store($market, $validated['images']);

        return back();
    }

    /**
     * Remove one gallery photo from a listing — owner only.
     */
    public function destroyImage(Market $market, MarketImage $image): RedirectResponse
    {
        Gate::authorize('update', $market);
        abort_unless($image->market_id === $market->id, 404);

        $this->marketImages->delete($image);

        return back();
    }

    /**
     * Display the active market page.
     */
    public function activeMarket(): Response
    {
        return Inertia::render('Market/ActiveMarketPage', [
            'lots' => $this->market->activeMarketListing(),
        ]);
    }

    /**
     * Display the full market analysis briefing for stakeholders — plain
     * -language stats plus a wider intelligence bundle (opportunities,
     * risks, competitors, trade flows, forecasts, currency, and top
     * trading parties) assembled from the platform's real data.
     */
    public function analyseMarket(): Response
    {
        $signals = $this->forecasts->signals();

        return Inertia::render('Market/MarketAnalysisPage', [
            'analysis' => $this->market->marketAnalysis(),
            'intel' => [
                'opportunities' => $this->market->marketOpportunities(),
                'competitors' => $this->market->competitorLandscape(),
                'tradeFlows' => $this->market->tradeFlows(),
                'forecast' => [
                    'price' => ForecastResource::collection($this->forecasts->horizons())->resolve(),
                    'demand' => ForecastResource::collection($signals->where('category', 'Demand')->values())->resolve(),
                    'weather' => ForecastResource::collection($signals->whereIn('category', ['Weather', 'Harvest'])->values())->resolve(),
                    'export' => ForecastResource::collection($signals->where('category', 'Export')->values())->resolve(),
                    'risk' => ForecastResource::collection($signals->where('direction', 'down')->values())->resolve(),
                ],
                'exchangeRates' => ExchangeRateResource::collection($this->exchangeRates->all())->resolve(),
                'topBuyers' => $this->orders->topBuyers(),
                'topSellers' => $this->orders->topSellers(),
            ],
        ]);
    }

    /**
     * Display the coffee production comparison tool for coffee-growing
     * countries, inside the market container so it sits alongside the
     * rest of the market's intelligence pages.
     */
    public function compareCountries(): Response
    {
        return Inertia::render('Market/ComparePage', [
            'producers' => CountryResource::collection($this->countries->coffeeProducers())->resolve(),
        ]);
    }
}
