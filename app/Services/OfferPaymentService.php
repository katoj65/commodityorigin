<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OfferPaymentService
{
    /**
     * Get a base query builder for offer payments.
     */
    public function query(): Builder
    {
        return OfferPayment::query();
    }

    /**
     * Record a payment for an offer, linked to the buyer's latest response
     * when one exists. Marks the payment completed so the offer can be
     * moved off the open offers board.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Offer $offer, int $userId, array $data): OfferPayment
    {
        $response = $offer->responses()
            ->where('user_id', $userId)
            ->latest()
            ->first();

        return OfferPayment::query()->create([
            'offer_id' => $offer->id,
            'offer_response_id' => $response?->id,
            'user_id' => $userId,
            'amount' => (float) $offer->total_amount,
            'currency' => $offer->currency,
            'payment_method' => $data['payment_method'] ?? null,
            'reference' => $this->generateReference(),
            'notes' => $data['notes'] ?? null,
            'status' => 'completed',
        ])->refresh();
    }

    /**
     * Generate a unique, human-readable payment reference.
     */
    public function generateReference(): string
    {
        do {
            $candidate = 'PAY-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        } while (OfferPayment::query()->where('reference', $candidate)->exists());

        return $candidate;
    }
}
