<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Services\AuctionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctions,
    ) {
    }

    /**
     * Display the seller's view of the auction hub — same page the
     * auction section's own index renders, since a seller's "My Auctions"
     * table lives there.
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
        ]);
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
     * Redirect the legacy "sell coffee" link to the market's Offers page.
     */
    public function sellCoffee(): RedirectResponse
    {
        return redirect()->route('market.offer');
    }

}
