<?php

namespace App\Http\Controllers\Exchange;

use App\Http\Controllers\Controller;
use App\Models\CropVarietyMetadata;
use App\Models\Offer;
use App\Services\CommodityOriginMetadataService;
use App\Services\MarketService;
use App\Services\OfferService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeController extends Controller
{
    public function __construct(
        private readonly MarketService $market,
        private readonly CommodityOriginMetadataService $origins,
        private readonly OfferService $offers,
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
            'openOffersCount' => $this->offers->countOpen(),
        ]);
    }

    /**
     * Display the Offers hub — pre-trade negotiation of price, Incoterms,
     * and escrow terms prior to binding order execution. Also handles the
     * Offers page's filter bar: the Filter button sends the current
     * search/origin/coffee type/status values back to this same route as
     * query params, and the page re-renders with the filtered offers.
     */
    public function offers(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'coffee_type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = $request->user()->id;

        $rows = $this->offers->allOrderedByStatus()
            ->map(fn (Offer $offer) => $this->offers->shapeForUser($offer, $userId))
            ->all();

        return Inertia::render('Exchange/OffersPage', [
            'originOptions' => $this->origins->activeNames(),
            'coffeeTypeOptions' => CropVarietyMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'offers' => $this->filterOffers($filters, $rows),
            'filters' => $filters,
        ]);
    }

    /**
     * Display a single offer's negotiation profile — the full commercial
     * terms, round-by-round history, and counterparty detail for one
     * offer, reached from the Offers hub table.
     */
    public function offerProfile(Request $request, Offer $offer): Response
    {
        $offer->load(['buyer', 'seller', 'market']);

        return Inertia::render('Exchange/OfferProfile', [
            'offerId' => $offer->offer_number,
            'offer' => $this->offers->shapeProfile($offer, $request->user()->id),
        ]);
    }

    /**
     * Apply the Offers page's filter bar to a set of offer rows: a
     * case-insensitive search across the offer id/name/counterparty, plus
     * exact-ish matches on origin, coffee type, and status.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function filterOffers(array $filters, array $rows): array
    {
        $search = isset($filters['search']) && $filters['search'] !== ''
            ? strtolower($filters['search'])
            : null;
        $origin = $filters['origin'] ?? null;
        $coffeeType = $filters['coffee_type'] ?? null;
        $status = $filters['status'] ?? null;

        return array_values(array_filter(
            $rows,
            function (array $offer) use ($search, $origin, $coffeeType, $status): bool {
                if ($search !== null) {
                    $haystack = strtolower($offer['id'].' '.$offer['name'].' '.$offer['counterparty']);
                    if (! str_contains($haystack, $search)) {
                        return false;
                    }
                }

                if ($origin && $origin !== 'all' && ! str_contains(strtolower($offer['origin']), strtolower($origin))) {
                    return false;
                }

                if ($coffeeType && $coffeeType !== 'all' && ! str_contains(strtolower($offer['name']), strtolower($coffeeType))) {
                    return false;
                }

                if ($status && ! in_array($status, ['all', 'active-first'], true) && ! str_contains(strtolower($offer['status']), strtolower($status))) {
                    return false;
                }

                return true;
            },
        ));
    }

    }
