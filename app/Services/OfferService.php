<?php

namespace App\Services;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

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
     * Get every offer on the marketplace, newest first.
     *
     * @return Collection<int, Offer>
     */
    public function all(): Collection
    {
        return Offer::query()
            ->with(['seller', 'buyer'])
            ->latest()
            ->get();
    }

    /**
     * Get every offer still open for a buyer to respond and pay for.
     *
     * @return Collection<int, Offer>
     */
    public function available(): Collection
    {
        return Offer::query()
            ->with(['seller', 'buyer'])
            ->where('status', 'open')
            ->latest()
            ->get();
    }

    /**
     * Post a new sell-side offer on behalf of a seller, with no buyer yet.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $sellerId): Offer
    {
        $quantity = (float) $data['quantity'];
        $unitPrice = (float) $data['unit_price'];

        return Offer::query()
            ->create([
                ...$data,
                'offer_number' => $this->generateOfferNumber(),
                'seller_id' => $sellerId,
                'total_amount' => round($quantity * $unitPrice, 2),
                'status' => 'open',
            ])
            ->refresh();
    }

    /**
     * Update an existing offer. Recomputes the total when quantity or unit
     * price changes.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Offer $offer, array $data): Offer
    {
        $offer->update($data);

        $offer->update([
            'total_amount' => round((float) $offer->quantity * (float) $offer->unit_price, 2),
        ]);

        return $offer->refresh();
    }

    /**
     * Delete an offer.
     */
    public function destroy(Offer $offer): void
    {
        $offer->delete();
    }

    /**
     * Generate a unique, human-readable offer number.
     */
    public function generateOfferNumber(): string
    {
        do {
            $candidate = 'OFR-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        } while (Offer::query()->where('offer_number', $candidate)->exists());

        return $candidate;
    }
}
