<?php

namespace App\Services;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferService
{
    public function __construct(
        private readonly OfferOrderService $offerOrders,
    ) {
    }

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
            ->with(['user', 'market.user'])
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
     * KPI stats for the Offers hub header row: total value of open offers,
     * how many are mid-negotiation or countered, and how many are open.
     *
     * @return array<string, mixed>
     */
    public function pipelineStats(): array
    {
        return [
            'pipelineValue' => (float) ($this->query()
                ->where('status', 'open')
                ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')
                ->value('total') ?? 0),
            'activeNegotiating' => $this->query()->where('status', 'negotiation')->count(),
            'offersSent' => $this->query()->where('status', 'counter_offer')->count(),
            'offersReceived' => $this->query()->where('status', 'open')->count(),
        ];
    }

    /**
     * Validate and apply a buyer's terms against an open offer listing —
     * the Offers hub's "Make an Offer" modal. Only offers still in `open`
     * status are eligible, and the listing's own seller (the referenced
     * market's owner) cannot make an offer on their own listing. On
     * success the offer moves into `pending` review carrying the buyer's
     * proposed quantity/price/notes.
     *
     * @param  array<string, mixed>  $data
     */
    public function submitOffer(Offer $offer, int $userId, array $data): Offer
    {
        if ($offer->status !== 'open') {
            throw ValidationException::withMessages([
                'status' => 'This offer is no longer open for new terms.',
            ]);
        }

        if ($offer->market && $offer->market->user_id === $userId) {
            throw ValidationException::withMessages([
                'status' => 'You cannot make an offer on your own listing.',
            ]);
        }

        $quantity = (float) $data['quantity'];
        $price = (float) $data['price'];
        $incoterm = $data['incoterm'];
        $notes = $data['message'] ?? null;

        return DB::transaction(function () use ($offer, $userId, $quantity, $price, $incoterm, $notes) {
            $offer->update([
                'user_id' => $userId,
                'quantity' => $quantity,
                'unit_price' => $price,
                'incoterm' => $incoterm,
                'total_amount' => $quantity * $price,
                'notes' => $notes,
                'status' => 'pending',
            ]);

            $offer->refresh();

            $this->offerOrders->createFromOffer($offer, $offer->incoterm);

            return $offer;
        });
    }

    /**
     * Shape an offer for the Offers hub table, from the perspective of the
     * given user (whichever side of the deal they're on). The seller is
     * derived from the offer's market listing (the listing's own `user`),
     * while the offer's own `user` is the buyer who placed it. Joined
     * against the market listing (via the eager-loaded `market` relation)
     * for the lot's real name/origin when the offer references one.
     *
     * @return array<string, mixed>
     */
    public function shapeForUser(Offer $offer, int $userId): array
    {
        $market = $offer->market;
        $seller = $market?->user;
        $buyer = $offer->user;
        $isSeller = $seller?->id === $userId;
        $counterparty = $isSeller ? $buyer : $seller;

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
        $market = $offer->market;
        $seller = $market?->user;
        $buyer = $offer->user;
        $isSeller = $seller?->id === $userId;

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
            'buyerName' => $buyer?->name,
            'sellerName' => $seller?->name,
            'counterpartyName' => $isSeller ? $buyer?->name : $seller?->name,
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
