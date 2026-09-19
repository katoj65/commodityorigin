<?php

namespace App\Http\Controllers\Exchange;

use App\Http\Controllers\Controller;
use App\Services\MarketService;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeSnapshotController extends Controller
{
    public function __construct(private readonly MarketService $market)
    {
    }

    /**
     * Display the public exchange snapshot — real, live activity across
     * the marketplace, visible without an account.
     */
    public function index(): Response
    {
        return Inertia::render('Exchange/Snapshot', [
            'analysis' => $this->market->marketAnalysis(),
            'demand' => $this->market->demandBreakdown(),
        ]);
    }
}
