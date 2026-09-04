<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Services\AuctionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuctionController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctions,
    ) {
    }

    /**
     * Display the coffee auction exchange.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Auction/AuctionPage', [
            'overview' => $this->auctions->overview(),
            'featuredLots' => $this->auctions->featuredLots(),
            'endingSoon' => $this->auctions->endingSoon(),
            'upcoming' => $this->auctions->upcoming(),
            'myBids' => $this->auctions->myBids($request->user()->id),
            'myAuctions' => $this->auctions->myAuctions($request->user()->id),
            'liveBids' => $this->auctions->liveBidFeed(),
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
}
