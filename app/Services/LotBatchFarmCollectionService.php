<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Lot;
use App\Models\LotBatchFarmCollection;
use Illuminate\Database\Eloquent\Collection;

/**
 * Maintains lot_batch_farm_collection — a denormalized 3-way pivot across
 * lots, batches, and farm_collections. It exists so pages that need a
 * lot's full custody chain (lot -> batch -> farm collection) can read one
 * table instead of joining lot_batch + batch_farm_collection every time.
 * It is a derived cache, not a source of truth: batch_farm_collection
 * remains authoritative for which farm collections fed a batch.
 */
class LotBatchFarmCollectionService
{
    /**
     * Copy a batch's own farm-collection links onto this lot, keyed by
     * (lot_id, batch_id, farm_collection_id) so re-syncing is idempotent —
     * each row's status is refreshed to match the batch's current link.
     * Call this whenever a batch is attached to a lot.
     */
    public function syncForLotBatch(Lot $lot, Batch $batch): void
    {
        $batch->loadMissing('batchFarmCollections');

        foreach ($batch->batchFarmCollections as $link) {
            LotBatchFarmCollection::query()->updateOrCreate(
                [
                    'lot_id' => $lot->id,
                    'batch_id' => $batch->id,
                    'farm_collection_id' => $link->farm_collection_id,
                ],
                [
                    'status' => $link->status,
                ],
            );
        }
    }

    /**
     * Remove this lot's copied links for a batch — call whenever that
     * batch is detached from the lot, so the cache doesn't go stale.
     */
    public function removeForLotBatch(Lot $lot, Batch $batch): void
    {
        LotBatchFarmCollection::query()
            ->where('lot_id', $lot->id)
            ->where('batch_id', $batch->id)
            ->delete();
    }

    /**
     * Get a lot's full custody chain of farm collections, across every
     * batch linked to it.
     *
     * @return Collection<int, LotBatchFarmCollection>
     */
    public function forLot(Lot $lot): Collection
    {
        return LotBatchFarmCollection::query()
            ->where('lot_id', $lot->id)
            ->with(['batch', 'farmCollection.farm'])
            ->get();
    }
}
