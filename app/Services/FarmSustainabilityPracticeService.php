<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\FarmSustainabilityPractice;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records and retrieves a farm's sustainability practices — free-text
 * entries (intercropping, organic composting, shade-grown cultivation,
 * etc.) shown on the farm's Sustainability Metrics card.
 */
class FarmSustainabilityPracticeService
{
    /**
     * Record a sustainability practice against a farm. `$practice` should
     * match a slug in sustainability_practices_metadata.
     */
    public function store(Farm $farm, string $practice, ?string $description, ?int $userId): FarmSustainabilityPractice
    {
        return FarmSustainabilityPractice::query()->create([
            'farm_id' => $farm->id,
            'user_id' => $userId,
            'practice' => $practice,
            'description' => $description,
        ]);
    }

    /**
     * Get a farm's sustainability practices, most recent first.
     *
     * @return Collection<int, FarmSustainabilityPractice>
     */
    public function forFarm(Farm $farm): Collection
    {
        return FarmSustainabilityPractice::query()
            ->where('farm_id', $farm->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Remove a mistaken or duplicate sustainability practice entry.
     */
    public function delete(FarmSustainabilityPractice $practice): void
    {
        $practice->delete();
    }
}
