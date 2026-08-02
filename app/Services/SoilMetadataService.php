<?php

namespace App\Services;

use App\Models\SoilMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SoilMetadataService
{
    /**
     * Get a base query builder for soil types.
     */
    public function query(): Builder
    {
        return SoilMetadata::query();
    }

    /**
     * Get every active soil type, ordered for display.
     */
    public function active(): Collection
    {
        return $this->query()->active()->get();
    }

    /**
     * Get the active soil type names available for a farm.
     */
    public function activeNames(): Collection
    {
        return $this->active()->pluck('name')->values();
    }
}
