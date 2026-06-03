<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Auction/AuctionPage');
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

    // Display the sell coffee page.
    public function sellCoffee(): Response
    {
        return Inertia::render('Sell/SellCoffeePage');
    }




    public function storeSell(Request $request)
    {
        $request->validate([
            'coffeeName'     => 'required|string|max:255',
            'coffeeType'     => 'required|string|max:100',
            'origin'         => 'required|string|max:100',
            'region'         => 'nullable|string|max:100',
            'process'        => 'nullable|string|max:100',
            'harvestSeason'  => 'nullable|string|max:50',
            'variety'        => 'nullable|string|max:100',
            'grade'          => 'nullable|string|max:50',
            'moisture'       => 'nullable|numeric|min:0|max:100',
            'cuppingScore'   => 'nullable|numeric|min:0|max:100',
            'description'    => 'nullable|string|max:2000',
            'saleType'       => 'required|in:fixed,auction,rfo',
            'quantity'       => 'required|numeric|min:1',
            'unit'           => 'required|string|max:20',
            'price'          => 'required|numeric|min:0',
            'minPrice'       => 'nullable|numeric|min:0',
            'currency'       => 'required|string|max:10',
            'markets'        => 'nullable|array',
            'buyerTypes'     => 'nullable|array',
            'certifications' => 'nullable|array',
            'traceability'   => 'boolean',
            'exportReady'    => 'boolean',
            'images'         => 'nullable|array|max:5',
            'images.*'       => 'image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        // TODO: persist listing and images to database.

        return redirect()->route('market.active')->with('success', 'Your listing has been published.');
    }





}
