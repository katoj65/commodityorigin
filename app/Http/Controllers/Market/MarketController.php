<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\Market;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $markets = Market::query()
            ->where('status', 'live')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Market $market): array => [
                'id'            => $market->id,
                'lot_code'      => $market->lot_code,
                'name'          => $market->name,
                'origin'        => $market->origin,
                'type'          => $market->type,
                'process'       => $market->process,
                'quality_score' => (float) ($market->quality_score ?? 0),
                'quantity'      => (float) ($market->quantity ?? 0),
                'price_per_kg'  => (float) ($market->price_per_kg ?? 0),
                'demand'        => $market->demand,
                'badges'        => $market->badges ?? [],
                'target_market' => $market->target_market,
                'status'        => $market->status,
                'image'         => $market->image,
            ]);

        return Inertia::render('Market/MarketPage', [
            'markets' => $markets,
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
        $lots = Market::query()
            ->where('status', 'live')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Market $market): array => [
                'id'           => $market->id,
                'lot_code'     => $market->lot_code,
                'name'         => $market->name,
                'origin'       => $market->origin,
                'type'         => $market->type,
                'price_per_kg' => (float) ($market->price_per_kg ?? 0),
                'quantity'     => (float) ($market->quantity ?? 0),
                'demand'       => $market->demand,
                'badges'       => $market->badges ?? [],
                'status'       => $market->status,
            ]);

        return Inertia::render('Market/LiveMarket', [
            'lots' => $lots,
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



    public function activeMarket(): Response
    {
        $lots = Market::query()
            ->where('status', 'live')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Market $market): array => [
                'id'           => $market->id,
                'code'         => $market->lot_code,
                'name'         => $market->name,
                'origin'       => $market->origin,
                'type'         => $market->type,
                'qty'          => number_format((float) ($market->quantity ?? 0)) . ' kg',
                'score'        => (float) ($market->quality_score ?? 0),
                'price'        => 'Shs. ' . number_format((float) ($market->price_per_kg ?? 0), 2),
                'demand'       => $market->demand,
                'seller'       => $market->seller ?? '—',
                'status'       => $market->status,
            ]);

        return Inertia::render('Market/ActiveMarketPage', [
            'lots' => $lots,
        ]);
    }





}
