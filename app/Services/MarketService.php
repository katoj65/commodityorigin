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
}
