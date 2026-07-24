<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Http\Resources\ExchangeRateResource;
use App\Http\Resources\ForecastResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\OrderResource;
use App\Services\BuyService;
use App\Services\CalendarService;
use App\Services\CountryService;
use App\Services\ExchangeRateService;
use App\Services\ForecastService;
use App\Services\MarketService;
use App\Services\OrderService;
use Illuminate\Http\Request;
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
