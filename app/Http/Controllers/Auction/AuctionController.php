<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\CropGradeMetadata;
use App\Models\CropVarietyMetadata;
use App\Models\Lot;
use App\Models\LotRequest;
use App\Services\AuctionService;
use App\Services\MarketService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuctionController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctions,
        private readonly MarketService $market,
    ) {
    }

    /**
     * Display the coffee auction exchange — every table that used to live
     * on the single auction page, now the "overview" landing page of the
     * auction section. Wrapped in the Trade hub's shared TradeLayout (as
     * the "Auctions" tab), so it needs the same tab-bar counts and Create
     * RFQ modal options every other Trade-hub page passes in.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Auction/Index', [
            'overview' => $this->auctions->overview($request->user()->id),
            'featuredLots' => $this->auctions->featuredLots(),
            'endingSoon' => $this->auctions->endingSoon(),
            'upcoming' => $this->auctions->upcoming(),
            'myBids' => $this->auctions->myBids($request->user()->id),
            'myAuctions' => $this->auctions->myAuctions($request->user()->id),
            'marketCount' => $this->market->liveCount(),
            'auctionCount' => Auction::query()->count(),
            'requestCount' => LotRequest::query()->count(),
            'cropTypeOptions' => CropVarietyMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'gradeOptions' => CropGradeMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
        ]);
    }

    /**
     * Live auctions — lots open for bidding right now, plus the ones
     * ending soonest.
     */
    public function live(Request $request): Response
    {
        return $this->renderPage($request, 'Auction/Live', [
            'featuredLots' => $this->auctions->featuredLots(),
            'endingSoon' => $this->auctions->endingSoon(),
        ]);
    }

    /**
     * Every bid the acting user has placed.
     */
    public function myBids(Request $request): Response
    {
        return $this->renderPage($request, 'Auction/MyBids', [
            'myBids' => $this->auctions->myBids($request->user()->id),
        ]);
    }

    /**
     * Every buyer who has placed at least one bid, ranked by total bid
     * value — the roster behind the "Active Buyers" KPI.
     */
    public function activeBuyers(Request $request): Response
    {
        return $this->renderPage($request, 'Auction/ActiveBuyers', [
            'buyers' => $this->auctions->activeBuyers(),
        ]);
    }

    /**
     * Display a single auction as the bidding workspace.
     */
    public function show(Request $request, Lot $lot): Response
    {
        $detail = $this->auctions->lotDetail($lot->id);

        abort_if($detail === null, 404);

        return Inertia::render('Auction/Show', [
            'lot' => $detail,
            'canBid' => in_array($request->user()?->role, ['buyer', 'admin'], true),
        ]);
    }

    /**
     * Render an auction-section page with the KPI overview every page
     * under the auction layout shares, merged with that page's own data.
     *
     * @param  array<string, mixed>  $data
     */
    private function renderPage(Request $request, string $component, array $data = []): Response
    {
        return Inertia::render($component, [
            'overview' => $this->auctions->overview($request->user()->id),
            ...$data,
        ]);
    }
}
