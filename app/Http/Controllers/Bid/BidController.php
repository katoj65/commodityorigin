<?php

namespace App\Http\Controllers\Bid;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Lot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Bid/BidPage');
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

    public function placeBid(Lot $lot): Response
    {
        return Inertia::render('Bid/PlaceBid', [
            'lot' => [
                'id'            => $lot->id,
                'lot_code'      => $lot->lot_code,
                'lot_name'      => $lot->lot_name,
                'origin'        => $lot->origin,
                'process'       => $lot->process,
                'grade'         => $lot->grade,
                'quality_score' => (float) ($lot->quality_score ?? 0),
                'price_per_kg'  => (float) ($lot->price_per_kg  ?? 0),
                'net_weight_kg' => (float) ($lot->net_weight_kg ?? 0),
                'image'         => $lot->image,
            ],
        ]);
    }



    public function storeBid(Request $request): RedirectResponse
    {
        $lot = Lot::findOrFail($request->input('lot_id'));

        $request->validate([
            'lot_id'     => ['required', 'integer', 'exists:lots,id'],
            'bid_amount' => ['required', 'numeric', 'min:' . ($lot->price_per_kg * 0.95)],
            'quantity'   => ['required', 'numeric'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        Bid::create([
            'lot_id'     => $lot->id,
            'user_id'    => auth()->id(),
            'bid_amount' => $request->input('bid_amount'),
            'quantity'   => $request->input('quantity'),
            'notes'      => $request->input('notes'),
            'status'     => 'pending',
        ]);

        return redirect()->route('bid.status');
    }




    public function bidStatus(): Response
    {
        return Inertia::render('Bid/BidStatus');
    }








}
