<?php

namespace App\Services;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OfferService
{
    /**
     * Get a base query builder for offers.
     */
    public function query(): Builder
    {
        return Offer::query();
    }

    /**
     * Get every offer in the marketplace, open offers surfaced first (so
     * buyers see what's actually available to act on before anything
     * already in progress or settled), newest within each group.
     *
     * @return Collection<int, Offer>
     */
    public function allOrderedByStatus(): Collection
    {
        return $this->query()
            ->with(['buyer', 'seller', 'market'])
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('status')
            ->latest()
            ->get();
    }

    /**
     * How many offers are currently open — awaiting a buyer/counterparty
     * action — for the Exchange page's Offers tab stat.
     */
    public function countOpen(): int
    {
        return $this->query()->where('status', 'open')->count();
    }

    /**
     * Shape an offer for the Offers hub table, from the perspective of the
     * given user (whichever side of the deal they're on). Joined against
     * its market listing (via the eager-loaded `market` relation) for the
     * lot's real name/origin when the offer references one.
     *
     * @return array<string, mixed>
     */
    public function shapeForUser(Offer $offer, int $userId): array
    {
        $isSeller = $offer->seller_id === $userId;
        $counterparty = $isSeller ? $offer->buyer : $offer->seller;
        $market = $offer->market;

        return [
            'recordId' => $offer->id,
            'id' => $offer->offer_number,
            'name' => $market?->name ?: trim("{$offer->crop_type} {$offer->variety} {$offer->grade}"),
            'origin' => $market?->origin ?: ($offer->variety ?: 'Uganda'),
            'qty' => number_format((float) $offer->quantity).' kg',
            'price' => '$'.number_format((float) $offer->unit_price, 2).'/kg',
            'counterparty' => $counterparty?->name ?? ($isSeller ? 'Awaiting buyer' : 'Unknown seller'),
            'counterpartyNote' => $isSeller ? 'Buyer' : 'Seller',
            'verified' => false,
            'validUntil' => 'Posted '.$offer->created_at->format('d M Y'),
            'status' => ucfirst($offer->status),
            'statusTone' => $this->statusTone($offer->status),
            'action' => 'View',
            'actionTone' => 'primary',
        ];
    }

    /**
     * Shape a single offer for the Offer Profile page, from the given
     * user's side of the deal. Only real, offers/market-backed fields are
     * returned here — the page itself fills in everything else
     * (negotiation rounds, audit trail, quality/provenance data, AI
     * commentary) with its own illustrative dummy content, since none of
     * that has a backing column yet.
     *
     * @return array<string, mixed>
     */
    public function shapeProfile(Offer $offer, int $userId): array
    {
        $isSeller = $offer->seller_id === $userId;
        $market = $offer->market;

        return [
            'commodity' => trim("{$offer->crop_type} {$offer->variety} {$offer->grade}"),
            'lotName' => $market?->name,
            'lotCode' => $market?->lot_code,
            'origin' => $market?->origin,
            'quantityKg' => (float) $offer->quantity,
            'unitPrice' => (float) $offer->unit_price,
            'totalAmount' => (float) $offer->total_amount,
            'currency' => $offer->currency,
            'status' => ucfirst($offer->status),
            'createdAt' => $offer->created_at?->format('d M Y, H:i'),
            'buyerName' => $offer->buyer?->name,
            'sellerName' => $offer->seller?->name,
            'counterpartyName' => $isSeller ? $offer->buyer?->name : $offer->seller?->name,
            'isSeller' => $isSeller,
            'marketPricePerKg' => $market?->price_per_kg !== null ? (float) $market->price_per_kg : null,
        ];
    }

    /**
     * The status pill's color tone.
     */
    private function statusTone(string $status): string
    {
        return match ($status) {
            'open' => 'primary',
            'pending' => 'secondary',
            'confirmed', 'delivered', 'bought' => 'accepted',
            'processing', 'shipped' => 'tertiary',
            'cancelled' => 'neutral',
            default => 'neutral',
        };
    }
}
