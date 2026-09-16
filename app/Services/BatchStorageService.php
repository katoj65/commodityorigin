<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchStorage;

/**
 * Records and retrieves a batch's dedicated storage detail via the
 * `batch_storages` table. One record per batch, so writes upsert rather
 * than append.
 */
class BatchStorageService
{
    /**
     * Create or update a batch's storage record.
     *
     * @param  array{storage_bay?: ?string, date_stored?: ?string, quantity_stored_kg?: ?float, climate_ambient?: ?string, physical_pallet?: ?string, packaging_spec?: ?string}  $data
     */
    public function store(Batch $batch, array $data): BatchStorage
    {
        return BatchStorage::query()->updateOrCreate(
            ['batch_id' => $batch->id],
            $data,
        );
    }

    /**
     * Get a batch's storage record, if one has been recorded.
     */
    public function forBatch(Batch $batch): ?BatchStorage
    {
        return BatchStorage::query()->where('batch_id', $batch->id)->first();
    }

    /**
     * Remove a batch's storage record.
     */
    public function delete(BatchStorage $storage): void
    {
        $storage->delete();
    }
}
