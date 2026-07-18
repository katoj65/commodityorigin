<?php

namespace App\Http\Controllers\Buy;

use App\Http\Controllers\Controller;
use App\Models\LotRequest;
use App\Services\BuyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BuyController extends Controller
{
    public function __construct(private readonly BuyService $buy)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Market/MarketPage');
    }

    /**
     * Submit a new coffee request on behalf of the authenticated buyer.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crop_type' => ['required', 'string', 'max:255'],
            'variety' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->buy->submitRequest($validated, $request->user()->id);

        return back()->with('success', 'Your coffee request has been submitted.');
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

    public function guestBuyCoffee(): Response
    {
        return Inertia::render('Market/MarketPage');
    }

    /**
     * Display the buyer page — submit coffee requests and browse open
     * requests from other buyers to discover and respond to.
     */
    public function buyer(Request $request): Response
    {
        $userId = $request->user()->id;

        return Inertia::render('Buy/BuyerPage', [
            'myRequests' => $this->buy->requestsForUser($userId),
            'openRequests' => $this->buy->openRequests($userId),
        ]);
    }

    /**
     * Respond to another buyer's open coffee request (approve, reject, or
     * mark fulfilled).
     */
    public function respond(Request $request, LotRequest $lotRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'fulfilled'])],
        ]);

        $this->buy->respond($lotRequest, $validated['status'], $request->user()->id);

        return back()->with('success', 'Response recorded.');
    }
}
