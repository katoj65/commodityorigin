<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\Farmer;
use App\Models\FarmerFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FarmerFarmService
{
    /**
     * Get a base query builder for farmer-farm links.
     */
    public function query(): Builder
    {
        return FarmerFarm::query();
    }

    /**
     * Get every farm linked to the given farmer, with the farm loaded.
     */
    public function forFarmer(int $farmerId): Collection
    {
        return $this->query()
            ->where('farmer_id', $farmerId)
            ->with('farm')
            ->latest()
            ->get();
    }

    /**
     * Get every farmer linked to the given farm, with the farmer loaded.
     */
    public function forFarm(int $farmId): Collection
    {
        return $this->query()
            ->where('farm_id', $farmId)
            ->with('farmer')
            ->latest()
            ->get();
    }

    /**
     * Link a farmer to a farm. `farm_code` is a denormalized copy of the
     * farm's own code, snapshotted at link time — matching the
     * batch_farm_collection pivot's convention. Idempotent: linking the
     * same farmer/farm pair twice returns the existing link.
     */
    public function attach(Farmer $farmer, Farm $farm, string $status = 'pending'): FarmerFarm
    {
        return $this->query()->firstOrCreate(
            [
                'farmer_id' => $farmer->id,
                'farm_id' => $farm->id,
            ],
            [
                'farm_code' => $farm->farm_code,
                'status' => $status,
            ],
        );
    }

    /**
     * Update a farmer-farm link's status.
     */
    public function updateStatus(FarmerFarm $link, string $status): FarmerFarm
    {
        $link->update(['status' => $status]);

        return $link;
    }

    /**
     * Remove a farmer-farm link.
     */
    public function detach(FarmerFarm $link): void
    {
        $link->delete();
    }
}
