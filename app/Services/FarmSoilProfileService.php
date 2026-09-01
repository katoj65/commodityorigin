<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\FarmSoilProfile;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records and retrieves a farm's soil profile entries — a history of
 * soil assessments (item + description) shown on the farm's Technical
 * Specs card.
 */
class FarmSoilProfileService
{
    /**
     * Record a soil profile entry against a farm. `$item` should match
     * a slug in soil_profile_metadata.
     */
    public function store(Farm $farm, string $item, ?string $description, ?int $userId): FarmSoilProfile
    {
        return FarmSoilProfile::query()->create([
            'farm_id' => $farm->id,
            'user_id' => $userId,
            'item' => $item,
            'description' => $description,
        ]);
    }

    /**
     * Get a farm's soil profile entries, most recent first.
     *
     * @return Collection<int, FarmSoilProfile>
     */
    public function forFarm(Farm $farm): Collection
    {
        return FarmSoilProfile::query()
            ->where('farm_id', $farm->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Remove a mistaken or duplicate soil profile entry.
     */
    public function delete(FarmSoilProfile $profile): void
    {
        $profile->delete();
    }
}
