<?php

namespace App\Services;

use App\Models\OriginMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OriginMetadataService
{
    /**
     * Get a base query builder for origins.
     */
    public function query(): Builder
    {
        return OriginMetadata::query();
    }

    /**
     * Get every active origin, ordered for display.
     */
    public function active(): Collection
    {
        return $this->query()->active()->get();
    }

    /**
     * Get the active origin names available for filters/forms.
     */
    public function activeNames(): Collection
    {
        return $this->active()->pluck('name')->values();
    }
}
