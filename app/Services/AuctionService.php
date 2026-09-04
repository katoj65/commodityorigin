<?php

namespace App\Services;

use App\Models\Bid;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AuctionService
{
    /**
     * Get a base query builder for auction lots.
     */
    public function query(): Builder
    {
        return Lot::query();
    }

    /**
     * Get every lot open for bidding (i.e. not still a draft), with the
     * relations the auction page needs eagerly loaded.
     *
     * @return Collection<int, Lot>
     */
    public function liveLots(): Collection
    {
        return Lot::query()
            ->where('status', '!=', 'draft')
            ->with(['lotBatches.batch', 'user', 'bids.user', 'sensoryProfile'])
            ->latest()
            ->get();
    }

    /**
     * Get every lot still in draft — not yet opened for bidding.
     *
     * @return Collection<int, Lot>
     */
    public function draftLots(): Collection
    {
        return Lot::query()
            ->where('status', 'draft')
            ->with(['lotBatches.batch', 'user'])
            ->latest()
            ->get();
    }

    /**
     * Headline KPIs for the auction overview hero.
     *
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $live = $this->liveLots();
        $drafts = $this->draftLots();
        $bids = Bid::query()->with('lot')->get();

        $totalAuctionValue = $live->sum(fn (Lot $lot) => $this->currentBid($lot) ?? (float) ($lot->price ?? 0));
        $highestBidToday = $bids->filter(fn (Bid $bid) => $bid->created_at?->isToday())->max('bid_amount');
        $activeBuyers = $bids->pluck('user_id')->unique()->count();

        return [
            'live_auctions' => $live->count(),
            'upcoming_lots' => $drafts->count(),
            'completed_auctions' => 0,
            'active_buyers' => $activeBuyers,
            'lots_available' => $live->count(),
            'total_auction_value' => round($totalAuctionValue, 2),
            'highest_bid_today' => $highestBidToday !== null ? round((float) $highestBidToday, 2) : null,
            'average_winning_price' => null,
            'ai_summary' => $this->buildSummary($live, $bids, $activeBuyers, $totalAuctionValue),
        ];
    }

    /**
     * Shape the largest/newest live lots as featured auction cards.
     *
     * @return array<int, array<string, mixed>>
     */
    public function featuredLots(int $limit = 6): array
    {
        return $this->liveLots()
            ->take($limit)
            ->map(fn (Lot $lot) => $this->shapeFeatured($lot))
            ->values()
            ->all();
    }

    /**
     * Lots approaching the end of their auction window — approximated as the
     * oldest live lots, since the app doesn't yet track explicit end times.
     *
     * @return array<int, array<string, mixed>>
     */
    public function endingSoon(int $limit = 4): array
    {
        return $this->liveLots()
            ->sortBy('created_at')
            ->take($limit)
            ->map(fn (Lot $lot) => $this->shapeFeatured($lot))
            ->values()
            ->all();
    }

    /**
     * Lots still in draft — not yet open for bidding.
     *
     * @return array<int, array<string, mixed>>
     */
    public function upcoming(): array
    {
        return $this->draftLots()
            ->map(fn (Lot $lot) => $this->shapeFeatured($lot))
            ->values()
            ->all();
    }

    /**
     * Bids placed by a given user, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function myBids(int $userId): array
    {
        return Bid::query()
            ->with('lot')
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn (Bid $bid) => [
                'id' => $bid->id,
                'lot_id' => $bid->lot_id,
                'lot_number' => $bid->lot?->lot_number ?? '—',
                'amount' => (float) $bid->bid_amount,
                'quantity' => (float) $bid->quantity,
                'status' => $bid->status,
                'placed_ago' => optional($bid->created_at)?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * Lots listed by a given user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function myAuctions(int $userId): array
    {
        return Lot::query()
            ->where('user_id', $userId)
            ->with(['bids.user', 'lotBatches.batch'])
            ->latest()
            ->get()
            ->map(fn (Lot $lot) => $this->shapeFeatured($lot))
            ->values()
            ->all();
    }

    /**
     * Shape every live lot for the coffee lot explorer table.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lotExplorer(): array
    {
        return $this->liveLots()
            ->map(fn (Lot $lot) => $this->shapeExplorerRow($lot))
            ->values()
            ->all();
    }

    /**
     * Get the most recent bids across every lot, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function liveBidFeed(int $limit = 20): array
    {
        return Bid::query()
            ->with(['lot', 'user'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn (Bid $bid) => [
                'id' => $bid->id,
                'lot_number' => $bid->lot?->lot_number ?? '—',
                'lot_id' => $bid->lot_id,
                'bidder' => $bid->user?->name ?? 'Unknown bidder',
                'amount' => (float) $bid->bid_amount,
                'quantity' => (float) $bid->quantity,
                'status' => $bid->status,
                'placed_at' => optional($bid->created_at)?->toIso8601String(),
                'placed_ago' => optional($bid->created_at)?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * Build a sensory/quality radar profile for a lot. Defaults to the
     * highest-quality live lot when none is specified.
     *
     * @return array<string, mixed>|null
     */
    public function qualityProfile(?int $lotId = null): ?array
    {
        $lot = $lotId
            ? Lot::query()->with(['sensoryProfile', 'batch'])->find($lotId)
            : $this->liveLots()->sortByDesc('quality_score')->first();

        if (! $lot) {
            return null;
        }

        $sensory = $lot->sensoryProfile;
        $cupScore = (float) ($lot->quality_score ?? $lot->batch?->cup_score ?? 0);

        return [
            'lot_id' => $lot->id,
            'lot_number' => $lot->lot_number,
            'axes' => [
                ['label' => 'Cup Score', 'value' => round($cupScore, 1)],
                ['label' => 'Aroma', 'value' => round((float) ($sensory->aroma_score ?? $lot->aroma_score ?? 0), 1)],
                ['label' => 'Acidity', 'value' => round((float) ($sensory->acidity_score ?? $lot->acidity_score ?? 0), 1)],
                ['label' => 'Body', 'value' => round((float) ($sensory->body_score ?? $lot->body_score ?? 0), 1)],
                ['label' => 'Aftertaste', 'value' => round((float) ($sensory->aftertaste_score ?? 0), 1)],
            ],
            'cupping_notes' => $sensory->cupping_notes ?? null,
            'flavor_notes' => $sensory->flavor_notes ?? null,
        ];
    }

    /**
     * Build a sensory/quality radar profile for every live lot, so
     * stakeholders can switch between lots on the quality intelligence
     * chart without an extra round trip.
     *
     * @return array<int, array<string, mixed>>
     */
    public function qualityProfiles(): array
    {
        return $this->liveLots()
            ->map(fn (Lot $lot) => $this->qualityProfile($lot->id))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Full lot detail payloads for every live lot, keyed by lot id, for the
     * lot details modal.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lotDetails(): array
    {
        return $this->liveLots()
            ->map(fn (Lot $lot) => $this->lotDetail($lot->id))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Auction analytics — origin performance, status volume, and bid
     * activity, all derived from real listings.
     *
     * @return array<string, mixed>
     */
    public function analytics(): array
    {
        $live = $this->liveLots();

        $topOrigins = $live->groupBy(fn (Lot $lot) => $this->originLabel($lot))
            ->map(fn (Collection $group, string $origin): array => [
                'label' => $origin,
                'count' => $group->count(),
                'average_price' => round((float) $group->avg('price'), 2),
                'total_volume_kg' => (float) $group->sum('net_weight_kg'),
            ])
            ->sortByDesc('average_price')
            ->take(6)
            ->values()
            ->all();

        $bidsPerLot = $live->map(fn (Lot $lot) => [
            'label' => $lot->lot_number,
            'bids' => $lot->bids->count(),
        ])->sortByDesc('bids')->take(6)->values()->all();

        $statusVolume = $live->groupBy('status')
            ->map(fn (Collection $group, string $status): array => ['label' => ucfirst(str_replace('_', ' ', $status)), 'count' => $group->count()])
            ->values()
            ->all();

        return [
            'top_origins' => $topOrigins,
            'bids_per_lot' => $bidsPerLot,
            'status_volume' => $statusVolume,
        ];
    }

    /**
     * Group live lots by origin for the origin intelligence section.
     *
     * @return array<int, array<string, mixed>>
     */
    public function originIntelligence(): array
    {
        return $this->liveLots()
            ->groupBy(fn (Lot $lot) => $this->originLabel($lot))
            ->map(fn (Collection $group, string $origin): array => [
                'label' => $origin,
                'lots' => $group->count(),
                'average_quality' => round((float) $group->avg('quality_score'), 1),
                'average_price' => round((float) $group->avg('price'), 2),
                'total_volume_kg' => (float) $group->sum('net_weight_kg'),
                'total_bids' => $group->sum(fn (Lot $lot) => $lot->bids->count()),
            ])
            ->sortByDesc('total_volume_kg')
            ->values()
            ->all();
    }

    /**
     * Heuristic AI-style recommendations computed from real listing and
     * bidding data — not a machine-learning model, but transparent math
     * over live numbers.
     *
     * @return array<string, mixed>
     */
    public function aiIntelligence(): array
    {
        $live = $this->liveLots();

        if ($live->isEmpty()) {
            return [
                'has_data' => false,
                'message' => 'No live lots yet — AI recommendations will appear once auctions are open.',
            ];
        }

        $undervalued = $live->sortByDesc(function (Lot $lot) {
            $price = (float) ($lot->price ?: 1);

            return ((float) ($lot->quality_score ?? 0)) / $price;
        })->first();

        $highestDemand = $live->sortByDesc(fn (Lot $lot) => $lot->bids->count())->first();

        $current = $this->currentBid($highestDemand) ?? (float) ($highestDemand->price ?? 0);
        $predictedWinningBid = round($current * 1.12, 2);
        $suggestedMaxBid = round($current * 1.18, 2);

        return [
            'has_data' => true,
            'most_undervalued_lot' => [
                'lot_number' => $undervalued->lot_number,
                'quality_score' => (float) ($undervalued->quality_score ?? 0),
                'price' => (float) ($undervalued->price ?? 0),
                'reason' => 'Highest quality-to-price ratio among live lots — strong cup quality relative to its current asking price.',
            ],
            'highest_demand_lot' => [
                'lot_number' => $highestDemand->lot_number,
                'bidder_count' => $highestDemand->bids->pluck('user_id')->unique()->count(),
                'bid_count' => $highestDemand->bids->count(),
                'reason' => 'Most active bidding activity of any live lot right now.',
            ],
            'predicted_winning_bid' => $predictedWinningBid,
            'suggested_maximum_bid' => $suggestedMaxBid,
            'confidence' => min(92, 55 + ($highestDemand->bids->count() * 8)),
            'note' => 'Predictions are a transparent heuristic (current bid + typical closing uplift), not a trained pricing model.',
        ];
    }

    /**
     * Top buyers (by bid value), top sellers (by listed lot value), and the
     * single largest auction by total bid value.
     *
     * @return array<string, mixed>
     */
    public function leaderboard(): array
    {
        $bids = Bid::query()->with('user')->get();
        $live = $this->liveLots();

        $topBuyers = $bids->groupBy('user_id')
            ->map(function (Collection $group) {
                $user = $group->first()->user;

                return [
                    'name' => $user?->name ?? 'Unknown buyer',
                    'role' => $user?->role,
                    'total_bid_value' => (float) $group->sum('bid_amount'),
                    'bids_placed' => $group->count(),
                ];
            })
            ->sortByDesc('total_bid_value')
            ->take(5)
            ->values()
            ->all();

        $topSellers = $live->groupBy('user_id')
            ->map(function (Collection $group) {
                $user = $group->first()->user;

                return [
                    'name' => $user?->name ?? 'Unknown seller',
                    'role' => $user?->role,
                    'total_listed_value' => (float) $group->sum('price'),
                    'lots_listed' => $group->count(),
                ];
            })
            ->sortByDesc('total_listed_value')
            ->take(5)
            ->values()
            ->all();

        $largestAuction = $live->sortByDesc(fn (Lot $lot) => $lot->bids->sum('bid_amount'))->first();

        $mostActiveOrigins = $live->groupBy(fn (Lot $lot) => $this->originLabel($lot))
            ->map(fn (Collection $group, string $origin) => ['label' => $origin, 'lots' => $group->count()])
            ->sortByDesc('lots')
            ->take(5)
            ->values()
            ->all();

        return [
            'top_buyers' => $topBuyers,
            'top_sellers' => $topSellers,
            'largest_auction' => $largestAuction ? [
                'lot_number' => $largestAuction->lot_number,
                'total_bid_value' => (float) $largestAuction->bids->sum('bid_amount'),
                'bid_count' => $largestAuction->bids->count(),
            ] : null,
            'most_active_origins' => $mostActiveOrigins,
        ];
    }

    /**
     * Build the full lot detail payload (used by the lot details modal),
     * including a real traceability timeline from actual timestamps.
     *
     * @return array<string, mixed>|null
     */
    public function lotDetail(int $lotId): ?array
    {
        $lot = Lot::query()->with(['lotBatches.batch', 'user', 'bids.user', 'sensoryProfile', 'storageProfile'])->find($lotId);

        if (! $lot) {
            return null;
        }

        $timeline = collect([
            $lot->batch?->processing_date ? ['label' => 'Processed', 'at' => Carbon::parse($lot->batch->processing_date)] : null,
            $lot->batch ? ['label' => 'Batch Created', 'at' => $lot->batch->created_at] : null,
            ['label' => 'Lot Created', 'at' => $lot->created_at],
            $lot->bids->isNotEmpty() ? ['label' => 'First Bid Received', 'at' => $lot->bids->sortBy('created_at')->first()->created_at] : null,
            $lot->bids->isNotEmpty() ? ['label' => 'Latest Bid', 'at' => $lot->bids->sortByDesc('created_at')->first()->created_at] : null,
        ])->filter()->sortBy('at')->values()
            ->map(fn (array $step) => ['label' => $step['label'], 'at' => optional($step['at'])->toIso8601String(), 'ago' => optional($step['at'])->diffForHumans()])
            ->all();

        return [
            ...$this->shapeFeatured($lot),
            'description' => $lot->description,
            'warehouse' => $lot->warehouse,
            'packaging_type' => $lot->packaging_type,
            'moisture_content' => $lot->batch?->moisture_content !== null ? (float) $lot->batch->moisture_content : null,
            'defect_count' => $lot->batch?->defect_count,
            'drying_method' => $lot->batch?->drying_method,
            'processing_method' => $lot->batch?->processing_method,
            'cupping_notes' => $lot->sensoryProfile?->cupping_notes,
            'flavor_notes' => $lot->sensoryProfile?->flavor_notes,
            'fragrance_notes' => $lot->sensoryProfile?->fragrance_notes,
            'timeline' => $timeline,
            'bid_history' => $lot->bids->sortByDesc('created_at')->map(fn (Bid $bid) => [
                'bidder' => $bid->user?->name ?? 'Unknown bidder',
                'amount' => (float) $bid->bid_amount,
                'placed_ago' => optional($bid->created_at)?->diffForHumans(),
                'status' => $bid->status,
            ])->values()->all(),
        ];
    }

    /**
     * The current highest bid for a lot, or null if it has none.
     */
    private function currentBid(Lot $lot): ?float
    {
        $max = $lot->bids->max('bid_amount');

        return $max !== null ? (float) $max : null;
    }

    /**
     * A readable origin label for a lot, sourced from its batch's warehouse
     * location.
     */
    private function originLabel(Lot $lot): string
    {
        return $lot->batch?->warehouse_location ?: 'Unspecified Origin';
    }

    /**
     * Shape a lot as a featured auction card.
     *
     * @return array<string, mixed>
     */
    private function shapeFeatured(Lot $lot): array
    {
        $current = $this->currentBid($lot);
        $starting = (float) ($lot->price ?? 0);
        $bidCount = $lot->bids->count();
        $bidderCount = $lot->bids->pluck('user_id')->unique()->count();
        $qualityScore = (float) ($lot->quality_score ?? $lot->batch?->cup_score ?? 0);

        return [
            'id' => $lot->id,
            'lot_number' => $lot->lot_number,
            'lot_name' => $lot->lot_name,
            'image' => $lot->image,
            'origin_country' => $this->originLabel($lot),
            'region' => $lot->region,
            'grower' => $lot->user?->name,
            'variety' => $lot->batch?->variety,
            'grade' => $lot->grade,
            'process' => $lot->process,
            'altitude' => $lot->altitude,
            'harvest_year' => $lot->batch?->processing_date ? Carbon::parse($lot->batch->processing_date)->year : null,
            'current_bid' => $current,
            'starting_price' => $starting,
            'min_increment' => round(max($starting * 0.02, 1), 2),
            'bidder_count' => $bidderCount,
            'bid_count' => $bidCount,
            'quality_score' => $qualityScore,
            'ai_score' => min(99, (int) round($qualityScore)),
            'status' => $lot->status,
            'net_weight_kg' => (float) ($lot->net_weight_kg ?? 0),
            'listed_ago' => optional($lot->created_at)?->diffForHumans(),
        ];
    }

    /**
     * Shape a lot as a row in the coffee lot explorer table.
     *
     * @return array<string, mixed>
     */
    private function shapeExplorerRow(Lot $lot): array
    {
        return [
            'id' => $lot->id,
            'lot_number' => $lot->lot_number,
            'origin' => $this->originLabel($lot),
            'region' => $lot->region,
            'grower' => $lot->user?->name,
            'variety' => $lot->batch?->variety,
            'grade' => $lot->grade,
            'moisture_content' => $lot->batch?->moisture_content !== null ? (float) $lot->batch->moisture_content : null,
            'screen_size' => $lot->screen_size ?? $lot->batch?->screen_size,
            'cup_score' => (float) ($lot->quality_score ?? $lot->batch?->cup_score ?? 0),
            'net_weight_kg' => (float) ($lot->net_weight_kg ?? 0),
            'reserve_price' => (float) ($lot->price ?? 0),
            'current_bid' => $this->currentBid($lot),
            'status' => $lot->status,
        ];
    }

    /**
     * A short, plain-language AI market summary sentence for the hero.
     */
    private function buildSummary(Collection $live, Collection $bids, int $activeBuyers, float $totalValue): string
    {
        if ($live->isEmpty()) {
            return 'No lots are open for bidding right now. Check back once sellers publish new lots to auction.';
        }

        return "There are {$live->count()} lots open for bidding worth an estimated " . number_format($totalValue) . ' in total, with ' . $activeBuyers . ' active ' . str('buyer')->plural($activeBuyers) . ' and ' . $bids->count() . ' ' . str('bid')->plural($bids->count()) . ' placed so far.';
    }
}
