<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\FarmCollection;
use App\Models\Lot;
use App\Models\Warehouse;

/**
 * Records and retrieves an item's bonded-warehousing/storage detail —
 * shared across batches, farm collections, and lots via the polymorphic
 * `warehouses` table. One record per item, so writes upsert rather than
 * append.
 */
class WarehouseService
{
    /**
     * Create or update an item's warehousing record.
     *
     * @param  array{storage_bay?: ?string, date_stored?: ?string, quantity_stored_kg?: ?float, climate_ambient?: ?string, physical_pallet?: ?string, packaging_spec?: ?string}  $data
     */
    public function store(Batch|FarmCollection|Lot $item, array $data): Warehouse
    {
        return Warehouse::query()->updateOrCreate(
            ['item_id' => $item->id, 'item_type' => $item->getMorphClass()],
            $data,
        );
    }

    /**
     * Get an item's warehousing record, if one has been recorded.
     */
    public function forItem(Batch|FarmCollection|Lot $item): ?Warehouse
    {
        return Warehouse::query()
            ->where('item_id', $item->id)
            ->where('item_type', $item->getMorphClass())
            ->first();
    }

    /**
     * Remove an item's warehousing record.
     */
    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }
}
