<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\LotSustainability;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records and retrieves a lot's sustainability practices — free-text
 * entries (deforestation-free sourcing, zero-water drying, fair producer
 * wage, etc.) shown on the lot's Sustainability & Compliance card.
 */
class LotSustainabilityService
{
    /**
     * Record a sustainability practice against a lot. `$practice` should
     * match a slug in sustainability_practices_metadata.
     */
    public function store(Lot $lot, string $practice, ?string $description, ?int $userId): LotSustainability
    {
        return LotSustainability::query()->create([
            'lot_id' => $lot->id,
            'user_id' => $userId,
            'practice' => $practice,
            'description' => $description,
        ]);
    }

    /**
     * Get a lot's sustainability practices, most recent first.
     *
     * @return Collection<int, LotSustainability>
     */
    public function forLot(Lot $lot): Collection
    {
        return LotSustainability::query()
            ->where('lot_id', $lot->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Remove a mistaken or duplicate sustainability practice entry.
     */
    public function delete(LotSustainability $practice): void
    {
        $practice->delete();
    }
}
