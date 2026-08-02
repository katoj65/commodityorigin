<?php

namespace App\Services;

use App\Models\ClimateZoneMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ClimateZoneMetadataService
{
    /**
     * Get a base query builder for climate zones.
     */
    public function query(): Builder
    {
        return ClimateZoneMetadata::query();
    }

    /**
     * Get every active climate zone, ordered for display.
     */
    public function active(): Collection
    {
        return $this->query()->active()->get();
    }

    /**
     * Get the active climate zone names available for a farm.
     */
    public function activeNames(): Collection
    {
        return $this->active()->pluck('name')->values();
    }
}
