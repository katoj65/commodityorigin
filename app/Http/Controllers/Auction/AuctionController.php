<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuctionResource;
use App\Http\Resources\CalendarResource;
use App\Services\AuctionListingService;
use App\Services\AuctionService;
use App\Services\CalendarService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuctionController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctions,
        private readonly CalendarService $calendar,
        private readonly AuctionListingService $auctionListings,
    ) {
    }

    /**
     * Display the coffee auction exchange.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Auction/AcutionPage', [
            'overview' => $this->auctions->overview(),
            'calendarEvents' => CalendarResource::collection(
                $this->calendar->eventsForUser($request->user()->id),
            )->resolve(),
            'featuredLots' => $this->auctions->featuredLots(),
            'auctionItems' => AuctionResource::collection($this->auctionListings->all())->resolve(),
            'liveBids' => $this->auctions->liveBidFeed(),
        ]);
    }
}
