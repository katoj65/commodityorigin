<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
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
            'liveBids' => $this->auctions->liveBidFeed(),
            'upcomingLots' => $this->auctions->draftLots()->map(fn ($lot) => [
                'id' => $lot->id,
                'lot_number' => $lot->lot_number,
                'origin' => $lot->batch?->warehouse_location,
                'grower' => $lot->user?->name,
                'listed_ago' => optional($lot->created_at)?->diffForHumans(),
            ])->values()->all(),
            'lotExplorer' => $this->auctions->lotExplorer(),
            'qualityProfiles' => $this->auctions->qualityProfiles(),
            'analytics' => $this->auctions->analytics(),
            'originIntelligence' => $this->auctions->originIntelligence(),
            'aiIntelligence' => $this->auctions->aiIntelligence(),
            'leaderboard' => $this->auctions->leaderboard(),
            'lotDetails' => $this->auctions->lotDetails(),
        ]);
    }
}
