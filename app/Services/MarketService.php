<?php

namespace App\Services;

use App\Http\Resources\LotImageResource;
use App\Http\Resources\MarketImageResource;
use App\Models\FarmSustainabilityPractice;
use App\Models\Market;
use App\Models\SustainabilityPracticesMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class MarketService
{
    public function __construct(private readonly LotService $lots)
    {
    }

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
            // Market::getImageAttribute() reads through to the lot's own
            // cover/gallery photo — eager-load both so that doesn't N+1.
            ->with('lot.images')
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
                'name' => $market->title,
                'origin' => $market->origin,
                'type' => $market->type,
                'process' => $market->process,
                'quality_score' => (float) ($market->quality_score ?? 0),
                'quantity' => (float) ($market->quantity ?? 0),
                'available_quantity' => (float) ($market->available_quantity ?? 0),
                'unit' => $market->unit,
                'currency' => $market->currency,
                'price_per_kg' => (float) ($market->price_per_unit ?? 0),
                'pricing_type' => $market->pricing_type,
                'demand' => $market->demand,
                'badges' => $market->badges ?? [],
                'target_market' => $market->target_market,
                'status' => $market->status,
                'is_featured' => (bool) $market->is_featured,
                'image' => $market->image,
            ])
            ->all();
    }

    /**
     * Query live markets narrowed down by the buyer's filter criteria —
     * coffee type, origin, process, price range, and minimum quality.
     * Type/origin/process/quality_score live in `metadata` now, not
     * dedicated columns, so they're filtered via JSON path queries.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Market>
     */
    public function filteredListing(array $filters): Collection
    {
        return $this->query()
            ->where('status', 'live')
            ->with('lot.images')
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('metadata->type', $type))
            ->when($filters['origin'] ?? null, fn (Builder $q, string $origin) => $q->where('metadata->origin', $origin))
            ->when($filters['process'] ?? null, fn (Builder $q, string $process) => $q->where('metadata->process', $process))
            ->when($filters['min_price'] ?? null, fn (Builder $q, $min) => $q->where('price_per_unit', '>=', $min))
            ->when($filters['max_price'] ?? null, fn (Builder $q, $max) => $q->where('price_per_unit', '<=', $max))
            ->when($filters['min_quality'] ?? null, fn (Builder $q, $min) => $q->where('metadata->quality_score', '>=', $min))
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
        $market->loadMissing([
            'user',
            'lot.user',
            'lot.lotBatches.batch.user',
            'lot.lotBatches.batch.batchFarmCollections.farmCollection.farm.farmers',
            'lot.storageProfile',
            'lot.blockchain',
            'lot.images',
            'images',
        ]);

        $lot = $market->lot;
        $batch = $lot?->batch;
        $storage = $lot?->storageProfile;

        // Reuse the traceability aggregation (already joins batches → farm
        // collections → farms → farmers) instead of re-walking the same
        // relations here, to surface real farm/harvest/supply-chain facts
        // for the product profile page.
        $trace = $lot ? $this->lots->traceabilityData($lot) : null;
        $primaryBatch = $trace['batches'][0] ?? null;
        $primaryFarm = $primaryBatch['farms'][0] ?? null;
        $primaryCollection = $primaryBatch['collections'][0] ?? null;
        $farmerCount = $trace['stats']['farmers'] ?? 0;
        $farmIds = collect($trace['farms'] ?? [])->pluck('id')->filter()->unique()->values()->all();

        $supplyChain = array_values(array_filter([
            ($primaryCollection['collection_date'] ?? null)
                ? ['label' => 'Harvest', 'date' => $primaryCollection['collection_date']]
                : null,
            ($primaryBatch['processing_date'] ?? null)
                ? ['label' => 'Processing', 'date' => $primaryBatch['processing_date']]
                : null,
            $market->created_at
                ? ['label' => 'Listed', 'date' => $market->created_at->format('d M Y')]
                : null,
        ]));

        $specs = array_filter([
            'grade' => $lot?->grade,
            'variety' => $batch?->variety,
            'screen' => $lot?->screen,
            'origin' => $lot?->origin,
            'region' => $lot?->region,
            'altitude' => $lot?->altitude,
            'moisture' => $this->toFloatOrNull($lot?->moisture),
            'year_of_harvest' => $lot?->year_of_harvest,
            'defects_percentage' => $this->toFloatOrNull($lot?->defects_percentage),
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

        // The lot's own acidity/body/flavor/aroma/aftertaste columns are the
        // live source of truth (metadata-slug dropdowns resolved to display
        // names) — traceabilityData() already resolved them onto $trace['lot'].
        $cupping = array_filter([
            'acidity' => $trace['lot']['acidity'] ?? null,
            'body' => $trace['lot']['body'] ?? null,
            'flavor' => $trace['lot']['flavor'] ?? null,
            'aroma' => $trace['lot']['aroma'] ?? null,
            'balance' => $trace['lot']['balance'] ?? null,
            'aftertaste' => $trace['lot']['aftertaste'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $marketAvgPrice = Market::query()
            ->where('status', 'live')
            ->where('metadata->type', $market->type)
            ->where('id', '!=', $market->id)
            ->avg('price_per_unit');

        $priceDeltaPct = $marketAvgPrice
            ? round((((float) $market->price_per_unit - $marketAvgPrice) / $marketAvgPrice) * 100, 1)
            : null;

        $sellerActiveListings = $market->user_id
            ? Market::where('user_id', $market->user_id)->where('status', 'live')->count()
            : null;

        return [
            'id' => $market->id,
            'lot_id' => $market->lot_id,
            'lot_code' => $market->lot_code,
            'name' => $market->title,
            'origin' => $market->origin,
            'type' => $market->type,
            'process' => $market->process ?? $batch?->processing_method,
            'quality_score' => $this->toFloatOrNull($market->quality_score),
            'quantity' => (float) ($market->quantity ?? 0),
            'available_quantity' => (float) ($market->available_quantity ?? 0),
            'unit' => $market->unit,
            'currency' => $market->currency,
            'price_per_kg' => (float) ($market->price_per_unit ?? 0),
            'pricing_type' => $market->pricing_type,
            'minimum_order_quantity' => $this->toFloatOrNull($market->minimum_order_quantity),
            'payment_terms' => $market->payment_terms,
            'delivery_terms' => $market->delivery_terms,
            'delivery_location' => $market->delivery_location,
            'demand' => $market->demand,
            'badges' => $market->badges ?? [],
            'target_market' => $market->target_market,
            'status' => $market->status,
            'is_featured' => (bool) $market->is_featured,
            'is_public' => (bool) $market->is_public,
            'notes' => $market->description ?: $lot?->description,
            'image' => $market->image,
            'images' => MarketImageResource::collection($market->images)->resolve(),
            'lot_image' => $lot?->image ? Storage::disk('public')->url($lot->image) : null,
            'lot_images' => $lot ? LotImageResource::collection($lot->images)->resolve() : [],
            'seller_name' => $market->user?->name,
            'created_at' => optional($market->created_at)?->toDateTimeString(),
            'specs' => $specs,
            'cupping' => $cupping ?: null,
            'market_avg_price_per_kg' => $marketAvgPrice ? round($marketAvgPrice, 2) : null,
            'price_delta_pct' => $priceDeltaPct,
            'seller_active_listings' => $sellerActiveListings,
            'is_traceable' => $market->lot_id !== null,
            'farm' => $primaryFarm ? [
                'name' => $primaryFarm['name'],
                'district' => $primaryFarm['district'],
                'region' => $primaryFarm['region'],
                'country' => $primaryFarm['country'],
                'latitude' => $primaryFarm['latitude'],
                'longitude' => $primaryFarm['longitude'],
            ] : null,
            'farmer_count' => $farmerCount ?: null,
            'harvest_season' => $primaryCollection['harvest_season'] ?? null,
            'supply_chain' => $supplyChain ?: null,
            // All sustainability practices recorded across every farm behind
            // this lot's batches — combined for the Sustainability column.
            'sustainability_practices' => $this->sustainabilityPracticesForFarms($farmIds),
            // Every distinct farm behind this lot's batches — for the "Source
            // Farms" list, not just the primary one used for the map pin.
            'contributing_farms' => collect($trace['farms'] ?? [])->map(fn ($farm) => [
                'id' => $farm['id'],
                'name' => $farm['name'],
                'size_ha' => $farm['coffee_area_ha'],
                'location' => $farm['location'],
            ])->values()->all(),
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
     * Collect the sustainability practices recorded across a set of farms,
     * resolved to their display names via the sustainability_practices_metadata
     * table.
     *
     * @param  array<int, mixed>  $farmIds
     * @return array<int, array<string, mixed>>
     */
    private function sustainabilityPracticesForFarms(array $farmIds): array
    {
        $farmIds = array_values(array_filter(array_map('intval', $farmIds)));

        if ($farmIds === []) {
            return [];
        }

        $practices = FarmSustainabilityPractice::query()
            ->whereIn('farm_id', $farmIds)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $names = SustainabilityPracticesMetadata::query()
            ->whereIn('slug', $practices->pluck('practice')->filter()->unique())
            ->pluck('name', 'slug');

        return $practices->map(fn (FarmSustainabilityPractice $practice): array => [
            'id' => $practice->id,
            'farm_id' => $practice->farm_id,
            'slug' => $practice->practice,
            'name' => $names->get($practice->practice) ?? ucwords(str_replace('_', ' ', (string) $practice->practice)),
            'description' => $practice->description,
        ])->values()->all();
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
                'name' => $market->title,
                'origin' => $market->origin,
                'type' => $market->type,
                'price_per_kg' => (float) ($market->price_per_unit ?? 0),
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
                'name' => $market->title,
                'origin' => $market->origin,
                'type' => $market->type,
                'qty' => number_format((float) ($market->quantity ?? 0)) . ' kg',
                'score' => (float) ($market->quality_score ?? 0),
                'price' => 'Shs. ' . number_format((float) ($market->price_per_unit ?? 0), 2),
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
        $averagePrice = (float) $listings->avg('price_per_unit');
        $averageQuality = (float) $listings->avg('quality_score');

        $types = $listings->groupBy(fn (Market $m) => ucfirst($m->type ?? 'Unspecified'))
            ->map(fn (Collection $group, string $type): array => [
                'label' => $type,
                'count' => $group->count(),
                'share' => round(($group->count() / $totalListings) * 100, 1),
                'average_price' => round((float) $group->avg('price_per_unit'), 2),
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
                'average_price' => round((float) $group->avg('price_per_unit'), 2),
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
            'min_price_per_kg' => (float) $listings->min('price_per_unit'),
            'max_price_per_kg' => (float) $listings->max('price_per_unit'),
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
                    'average_price' => round((float) $group->avg('price_per_unit'), 2),
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
                    'average_price' => round((float) $group->avg('price_per_unit'), 2),
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
                    'average_price' => round((float) $group->avg('price_per_unit'), 2),
                ];
            })
            ->sortByDesc('volume_kg')
            ->take(5)
            ->values()
            ->all();
    }
}
