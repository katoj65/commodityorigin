<?php

namespace App\Http\Controllers\Exchange;

use App\Http\Controllers\Controller;
use App\Models\CropVarietyMetadata;
use App\Services\CountryService;
use App\Services\MarketService;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeController extends Controller
{
    public function __construct(
        private readonly MarketService $market,
        private readonly CountryService $countries,
    ) {
    }

    /**
     * Display the institutional trading floor — the authenticated
     * exchange terminal for browsing and trading live physical lots.
     */
    public function index(): Response
    {
        $paginator = $this->market->exchangeListing(10);

        return Inertia::render('Exchange/Index', [
            'lots' => [
                'data' => array_values($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Display the Offers hub — pre-trade negotiation of price, Incoterms,
     * and escrow terms prior to binding order execution.
     */
    public function offers(): Response
    {
        return Inertia::render('Exchange/OffersPage', [
            'originOptions' => $this->countries->coffeeProducers()->pluck('name')->values(),
            'coffeeTypeOptions' => CropVarietyMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
        ]);
    }
}
