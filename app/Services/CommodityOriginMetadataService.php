<?php

namespace App\Services;

use App\Models\CommodityOriginMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CommodityOriginMetadataService
{
    /**
     * Get a base query builder for commodity origins.
     */
    public function query(): Builder
    {
        return CommodityOriginMetadata::query();
    }

    /**
     * Get every active commodity origin, ordered for display.
     */
    public function active(): Collection
    {
        return $this->query()->active()->get();
    }

    /**
     * Get the active commodity origin names available for filters/forms.
     */
    public function activeNames(): Collection
    {
        return $this->active()->pluck('name')->values();
    }
}
