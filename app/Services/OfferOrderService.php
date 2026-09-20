<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class OfferOrderService
{
    /**
     * Get a base query builder for offer orders.
     */
    public function query(): Builder
    {
        return OfferOrder::query();
    }

    /**
     * Every offer order belonging to the given user, newest first.
     *
     * @return Collection<int, OfferOrder>
     */
    public function allForUser(int $userId): Collection
    {
        return $this->query()
            ->with(['user', 'offer', 'market'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Execute an offer into an order: snapshots the offer's quantity,
     * price, Incoterms port, and market listing into a new offer_orders
     * row, linked back to the source offer. Only offers that have moved
     * past `open` (i.e. a buyer's terms have been accepted) are eligible.
     */
    public function createFromOffer(Offer $offer, ?string $incoterm = null): OfferOrder
    {
        if ($offer->status === 'open' || $offer->user_id === null) {
            throw ValidationException::withMessages([
                'status' => 'This offer has no accepted buyer terms to execute into an order yet.',
            ]);
        }

        return $this->query()->create([
            'user_id' => $offer->user_id,
            'offer_id' => $offer->id,
            'market_id' => $offer->market_id,
            'quantity' => $offer->quantity,
            'unit_price' => $offer->unit_price,
            'incoterm' => $incoterm,
            'notes' => $offer->notes,
            'status' => 'pending',
            'executed_at' => now(),
        ]);
    }
}
