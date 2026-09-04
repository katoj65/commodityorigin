<?php

namespace App\Services;

use App\Models\PriceIndex;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PriceIndexService
{
    /**
     * Get a base query builder for price index entries.
     */
    public function query(): Builder
    {
        return PriceIndex::query();
    }

    /**
     * Get every price index entry, ordered by item.
     *
     * @return Collection<int, PriceIndex>
     */
    public function all(): Collection
    {
        return PriceIndex::query()
            ->orderBy('item')
            ->get();
    }

    /**
     * Find the price index entry for a specific item.
     */
    public function forItem(string $item): ?PriceIndex
    {
        return PriceIndex::query()
            ->where('item', $item)
            ->first();
    }

    /**
     * Create or update the price index entry for an item.
     *
     * @param  array<string, mixed>  $data
     */
    public function upsert(string $item, array $data): PriceIndex
    {
        return PriceIndex::query()->updateOrCreate(
            ['item' => $item],
            $data,
        )->refresh();
    }

    /**
     * Delete a price index entry.
     */
    public function destroy(PriceIndex $priceIndex): void
    {
        $priceIndex->delete();
    }
}
