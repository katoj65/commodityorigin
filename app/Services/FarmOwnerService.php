<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\FarmOwner;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records and retrieves a farm's registered owner(s) — separate from
 * `farms.user_id` (the platform account managing the farm) and from
 * `farmers` (the smallholders who deliver produce). A farm can have more
 * than one owner, for co-ownership.
 */
class FarmOwnerService
{
    /**
     * Record an owner against a farm.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(Farm $farm, array $data, ?int $userId): FarmOwner
    {
        return FarmOwner::query()->create([
            ...$data,
            'farm_id' => $farm->id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Get a farm's owners, primary first then most recently recorded.
     *
     * @return Collection<int, FarmOwner>
     */
    public function forFarm(Farm $farm): Collection
    {
        return FarmOwner::query()
            ->where('farm_id', $farm->id)
            ->with('user')
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Update an existing owner entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(FarmOwner $owner, array $data): FarmOwner
    {
        $owner->update($data);

        return $owner;
    }

    /**
     * Remove a mistaken or duplicate owner entry.
     */
    public function delete(FarmOwner $owner): void
    {
        $owner->delete();
    }
}
