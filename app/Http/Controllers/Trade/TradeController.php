<?php

namespace App\Http\Controllers\Trade;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\LotRequest;
use App\Services\MarketService;
use Inertia\Inertia;
use Inertia\Response;

class TradeController extends Controller
{
    public function __construct(
        private readonly MarketService $market,
    ) {
    }

    /**
     * Display the trade hub.
     */
    public function index(): Response
    {
        return Inertia::render('Trade/Index', [
            'markets' => $this->market->marketPageListing(),
            'auctionCount' => Auction::query()->count(),
            'requestCount' => LotRequest::query()->count(),
        ]);
    }
}
