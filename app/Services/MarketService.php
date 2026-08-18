<?php

namespace App\Services;

use App\Models\Market;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MarketService
{
    /**
     * Get a base query builder for markets.
     */
    public function query(): Builder
    {
        return Market::query();
    }

    /**
     * Get every live market listing, newest first.
     *
     * @return Collection<int, Market>
     */
    public function liveMarkets(): Collection
    {
        return Market::query()
            ->where('status', 'live')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Shape live markets for the main market directory page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function marketPageListing(): array
    {
        return $this->liveMarkets()
            ->map(fn (Market $market): array => [
                'id' => $market->id,
                'lot_code' => $market->lot_code,
                'name' => $market->name,
                'origin' => $market->origin,
                'type' => $market->type,
                'process' => $market->process,
                'quality_score' => (float) ($market->quality_score ?? 0),
                'quantity' => (float) ($market->quantity ?? 0),
                'price_per_kg' => (float) ($market->price_per_kg ?? 0),
                'demand' => $market->demand,
                'badges' => $market->badges ?? [],
                'target_market' => $market->target_market,
                'status' => $market->status,
                'image' => $market->image,
            ])
            ->all();
    }

    /**
     * Query live markets narrowed down by the buyer's filter criteria —
     * coffee type, origin, process, price range, and minimum quality.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Market>
     */
    public function filteredListing(array $filters): Collection
    {
        return $this->query()
            ->where('status', 'live')
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type))
            ->when($filters['origin'] ?? null, fn (Builder $q, string $origin) => $q->where('origin', $origin))
            ->when($filters['process'] ?? null, fn (Builder $q, string $process) => $q->where('process', $process))
            ->when($filters['min_price'] ?? null, fn (Builder $q, $min) => $q->where('price_per_kg', '>=', $min))
            ->when($filters['max_price'] ?? null, fn (Builder $q, $max) => $q->where('price_per_kg', '<=', $max))
            ->when($filters['min_quality'] ?? null, fn (Builder $q, $min) => $q->where('quality_score', '>=', $min))
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * The distinct coffee types, origins, and processes available across
     * live listings — populates the market filter dialog's dropdowns.
     *
     * @return array<string, array<int, string>>
     */
    public function filterOptions(): array
    {
        $listings = $this->liveMarkets();

        return [
            'types' => $listings->pluck('type')->filter()->unique()->sort()->values()->all(),
            'origins' => $listings->pluck('origin')->filter()->unique()->sort()->values()->all(),
            'processes' => $listings->pluck('process')->filter()->unique()->sort()->values()->all(),
        ];
    }

    /**
     * Shape a single market listing for its product profile page — pulls in
     * the underlying lot's batch/sensory/storage records when the listing
     * was posted from a tracked lot, for a fuller spec sheet.
     *
     * @return array<string, mixed>
     */
    public function show(Market $market): array
    {
        $market->loadMissing(['user', 'lot.batch', 'lot.sensoryProfile', 'lot.storageProfile']);

        $lot = $market->lot;
        $batch = $lot?->batch;
        $sensory = $lot?->sensoryProfile;
        $storage = $lot?->storageProfile;

        $specs = array_filter([
            'grade' => $lot?->grade,
            'variety' => $batch?->variety,
            'screen_size' => $lot?->screen_size,
            'altitude' => $lot?->altitude,
            'moisture_content' => $this->toFloatOrNull($batch?->moisture_content),
            'defect_count' => $batch?->defect_count,
            'drying_method' => $batch?->drying_method,
            'processing_date' => optional($batch?->processing_date)?->toDateString(),
            'packaging_type' => $lot?->packaging_type ?? $storage?->packaging_type,
            'net_weight_kg' => $this->toFloatOrNull($lot?->net_weight_kg ?? $storage?->net_weight_kg),
            'quantity_bags' => $lot?->quantity_bags ?? $storage?->quantity_bags,
            'bag_weight_kg' => $this->toFloatOrNull($lot?->bag_weight_kg ?? $storage?->bag_weight_kg),
            'warehouse' => $lot?->warehouse ?? $storage?->warehouse,
        ], fn ($value) => $value !== null && $value !== '');

        $cupping = array_filter([
            'aroma_score' => $this->toFloatOrNull($sensory?->aroma_score ?? $lot?->aroma_score),
            'acidity_score' => $this->toFloatOrNull($sensory?->acidity_score ?? $lot?->acidity_score),
            'body_score' => $this->toFloatOrNull($sensory?->body_score ?? $lot?->body_score),
            'aftertaste_score' => $this->toFloatOrNull($sensory?->aftertaste_score),
            'flavor_notes' => $sensory?->flavor_notes,
            'fragrance_notes' => $sensory?->fragrance_notes,
            'cupping_notes' => $sensory?->cupping_notes,
        ], fn ($value) => $value !== null && $value !== '');

        $marketAvgPrice = Market::query()
            ->where('status', 'live')
            ->where('type', $market->type)
            ->where('id', '!=', $market->id)
            ->avg('price_per_kg');

        $priceDeltaPct = $marketAvgPrice
            ? round((((float) $market->price_per_kg - $marketAvgPrice) / $marketAvgPrice) * 100, 1)
            : null;

        $sellerActiveListings = $market->user_id
            ? Market::where('user_id', $market->user_id)->where('status', 'live')->count()
            : null;

        return [
            'id' => $market->id,
            'lot_id' => $market->lot_id,
            'lot_code' => $market->lot_code,
            'name' => $market->name,
            'origin' => $market->origin,
            'type' => $market->type,
            'process' => $market->process ?? $batch?->processing_method,
            'quality_score' => (float) ($market->quality_score ?? 0),
            'quantity' => (float) ($market->quantity ?? 0),
            'price_per_kg' => (float) ($market->price_per_kg ?? 0),
            'demand' => $market->demand,
            'badges' => $market->badges ?? [],
            'target_market' => $market->target_market,
            'status' => $market->status,
            'notes' => $market->notes ?: $lot?->description,
            'image' => $market->image,
            'seller_name' => $market->user?->name,
            'created_at' => optional($market->created_at)?->toDateTimeString(),
            'specs' => $specs,
            'cupping' => $cupping ?: null,
            'market_avg_price_per_kg' => $marketAvgPrice ? round($marketAvgPrice, 2) : null,
            'price_delta_pct' => $priceDeltaPct,
            'seller_active_listings' => $sellerActiveListings,
            'is_traceable' => $market->lot_id !== null,
        ];
    }

    /**
     * Null-safe cast — decimal-cast attributes surface as strings, and
     * absent relations surface as null; this keeps both sane as floats.
     */
    private function toFloatOrNull(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * Shape live markets for the live market terminal.
     *
     * @return array<int, array<string, mixed>>
     */
    public function liveMarketListing(): array
    {
        return $this->liveMarkets()
            ->map(fn (Market $market): array => [
                'id' => $market->id,
                'lot_code' => $market->lot_code,
                'name' => $market->name,
                'origin' => $market->origin,
                'type' => $market->type,
                'price_per_kg' => (float) ($market->price_per_kg ?? 0),
                'quantity' => (float) ($market->quantity ?? 0),
                'demand' => $market->demand,
                'badges' => $market->badges ?? [],
                'status' => $market->status,
            ])
            ->all();
    }

    /**
     * Shape live markets for the active market page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function activeMarketListing(): array
    {
        return $this->liveMarkets()
            ->map(fn (Market $market): array => [
                'id' => $market->id,
                'code' => $market->lot_code,
                'name' => $market->name,
                'origin' => $market->origin,
                'type' => $market->type,
                'qty' => number_format((float) ($market->quantity ?? 0)) . ' kg',
                'score' => (float) ($market->quality_score ?? 0),
                'price' => 'Shs. ' . number_format((float) ($market->price_per_kg ?? 0), 2),
                'demand' => $market->demand,
                'seller' => $market->seller ?? '—',
                'status' => $market->status,
            ])
            ->all();
    }

    /**
     * Build a plain-language market analysis from live listings — the
     * headline numbers, a breakdown by coffee type, origin, and demand
     * level, and a handful of simple, readable takeaways for stakeholders.
     *
     * @return array<string, mixed>
     */
    public function marketAnalysis(): array
    {
        $listings = $this->liveMarkets();
        $totalListings = $listings->count();

        if ($totalListings === 0) {
            return [
                'total_listings' => 0,
                'total_volume_kg' => 0,
                'average_price_per_kg' => null,
                'min_price_per_kg' => null,
                'max_price_per_kg' => null,
                'average_quality_score' => null,
                'types' => [],
                'origins' => [],
                'demand' => [],
                'insights' => [
                    'There are no live listings on the market yet — insights will appear here as soon as sellers start listing coffee.',
                ],
            ];
        }

        $totalVolume = (float) $listings->sum('quantity');
        $averagePrice = (float) $listings->avg('price_per_kg');
        $averageQuality = (float) $listings->avg('quality_score');

        $types = $listings->groupBy(fn (Market $m) => ucfirst($m->type ?? 'Unspecified'))
            ->map(fn (Collection $group, string $type): array => [
                'label' => $type,
                'count' => $group->count(),
                'share' => round(($group->count() / $totalListings) * 100, 1),
                'average_price' => round((float) $group->avg('price_per_kg'), 2),
                'total_volume_kg' => (float) $group->sum('quantity'),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        $origins = $listings->groupBy(fn (Market $m) => ucfirst($m->origin ?? 'Unspecified'))
            ->map(fn (Collection $group, string $origin): array => [
                'label' => $origin,
                'count' => $group->count(),
                'total_volume_kg' => (float) $group->sum('quantity'),
                'average_price' => round((float) $group->avg('price_per_kg'), 2),
            ])
            ->sortByDesc('total_volume_kg')
            ->take(5)
            ->values()
            ->all();

        $demand = $listings->filter(fn (Market $m) => filled($m->demand))
            ->groupBy(fn (Market $m) => ucfirst($m->demand))
            ->map(fn (Collection $group, string $level): array => [
                'label' => $level,
                'count' => $group->count(),
                'share' => round(($group->count() / $totalListings) * 100, 1),
            ])
            ->values()
            ->all();

        $topType = collect($types)->first();
        $topOrigin = collect($origins)->first();

        $insights = array_filter([
            "There are {$totalListings} live " . str('listing')->plural($totalListings) . " on the market totaling " . number_format($totalVolume) . ' kg of coffee.',
            'The average price across all live listings is ' . number_format($averagePrice, 2) . ' per kg.',
            $averageQuality > 0 ? 'Listings average a quality score of ' . number_format($averageQuality, 1) . ' out of 100.' : null,
            $topType ? "{$topType['label']} is the most listed coffee type, making up {$topType['share']}% of the market." : null,
            $topOrigin ? "{$topOrigin['label']} leads by volume, with " . number_format($topOrigin['total_volume_kg']) . ' kg listed.' : null,
        ]);

        return [
            'total_listings' => $totalListings,
            'total_volume_kg' => $totalVolume,
            'average_price_per_kg' => round($averagePrice, 2),
            'min_price_per_kg' => (float) $listings->min('price_per_kg'),
            'max_price_per_kg' => (float) $listings->max('price_per_kg'),
            'average_quality_score' => $averageQuality > 0 ? round($averageQuality, 1) : null,
            'types' => $types,
            'origins' => $origins,
            'demand' => $demand,
            'insights' => array_values($insights),
        ];
    }

    /**
     * Surface coffee types where buyer demand is outpacing available
     * supply — a simple, real gap between each type's share of high-demand
     * listings and its share of total listings.
     *
     * @return array<int, array<string, mixed>>
     */
    public function marketOpportunities(): array
    {
        $listings = $this->liveMarkets();
        $total = $listings->count();

        if ($total === 0) {
            return [];
        }

        return $listings->groupBy(fn (Market $m) => ucfirst($m->type ?? 'Unspecified'))
            ->map(function (Collection $group, string $type) use ($total): array {
                $count = $group->count();
                $share = $count / $total;
                $highDemandCount = $group->filter(fn (Market $m) => strtolower((string) $m->demand) === 'high')->count();
                $highDemandShare = $count > 0 ? $highDemandCount / $count : 0;

                return [
                    'type' => $type,
                    'listings' => $count,
                    'share' => round($share * 100, 1),
                    'high_demand_share' => round($highDemandShare * 100, 1),
                    'gap_score' => round(($highDemandShare - $share) * 100, 1),
                    'average_price' => round((float) $group->avg('price_per_kg'), 2),
                ];
            })
            ->filter(fn (array $row) => $row['gap_score'] > 0)
            ->sortByDesc('gap_score')
            ->take(4)
            ->values()
            ->all();
    }

    /**
     * Aggregate live listings by origin and destination market — the
     * clearest real proxy this app has for coffee trade flow volume.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tradeFlows(): array
    {
        return $this->liveMarkets()
            ->filter(fn (Market $m) => filled($m->origin) && filled($m->target_market))
            ->groupBy(fn (Market $m) => $m->origin . '|' . $m->target_market)
            ->map(function (Collection $group): array {
                $first = $group->first();

                return [
                    'origin' => $first->origin,
                    'destination' => $first->target_market,
                    'listings' => $group->count(),
                    'volume_kg' => (float) $group->sum('quantity'),
                    'average_price' => round((float) $group->avg('price_per_kg'), 2),
                ];
            })
            ->sortByDesc('volume_kg')
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * Rank the sellers currently competing for buyer attention on the live
     * market — listing count, volume, and average quality/price per seller.
     *
     * @return array<int, array<string, mixed>>
     */
    public function competitorLandscape(): array
    {
        $listings = Market::query()
            ->where('status', 'live')
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        if ($listings->isEmpty()) {
            return [];
        }

        return $listings->groupBy('user_id')
            ->map(function (Collection $group): array {
                $seller = $group->first()->user;

                return [
                    'name' => $seller?->name ?? 'Seller',
                    'listings' => $group->count(),
                    'volume_kg' => (float) $group->sum('quantity'),
                    'average_quality' => round((float) $group->avg('quality_score'), 1),
                    'average_price' => round((float) $group->avg('price_per_kg'), 2),
                ];
            })
            ->sortByDesc('volume_kg')
            ->take(5)
            ->values()
            ->all();
    }
}
