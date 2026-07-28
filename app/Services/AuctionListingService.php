<?php

namespace App\Services;

use App\Models\Auction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AuctionListingService
{
    /**
     * Get a base query builder for auction catalog items.
     */
    public function query(): Builder
    {
        return Auction::query();
    }

    /**
     * Get every auction catalog item, newest first.
     *
     * @return Collection<int, Auction>
     */
    public function all(): Collection
    {
        return Auction::query()
            ->with('user')
            ->latest()
            ->get();
    }

    /**
     * Find a single auction catalog item.
     */
    public function find(int $id): Auction
    {
        return Auction::query()->with('user')->findOrFail($id);
    }

    /**
     * Create a new auction catalog item.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Auction
    {
        return Auction::query()->create($data)->refresh();
    }

    /**
     * Update an existing auction catalog item.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Auction $auction, array $data): Auction
    {
        $auction->update($data);

        return $auction;
    }

    /**
     * Delete an auction catalog item.
     */
    public function destroy(Auction $auction): void
    {
        $auction->delete();
    }
}
