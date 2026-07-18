<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Http\Resources\ExchangeRateResource;
use App\Services\CalendarService;
use App\Services\ExchangeRateService;
use App\Services\MarketService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketController extends Controller
{
    public function __construct(
        private readonly MarketService $market,
        private readonly CalendarService $calendar,
        private readonly ExchangeRateService $exchangeRates,
    ) {
    }

    /**
     * Display the market directory.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Market/MarketPage', [
            'markets' => $this->market->marketPageListing(),
            'calendarEvents' => CalendarResource::collection(
                $this->calendar->eventsForUser($request->user()->id),
            )->resolve(),
            'exchangeRates' => ExchangeRateResource::collection($this->exchangeRates->all())->resolve(),
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
     * Display a simple, plain-language market analysis for stakeholders.
     */
    public function analyseMarket(): Response
    {
        return Inertia::render('Market/MarketAnalysisPage', [
            'analysis' => $this->market->marketAnalysis(),
        ]);
    }
}
