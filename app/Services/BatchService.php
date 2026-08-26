<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchCompliance;
use App\Models\BatchFarmCollection;
use App\Models\FarmCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BatchService
{
    /**
     * Look up a batch by its batch_number, returning its full details
     * (or null if no batch has that number).
     */
    public function findByNumber(string $batchNumber): ?Batch
    {
        return Batch::query()->where('batch_number', $batchNumber)->first();
    }

    /**
     * Paginate batches, optionally filtered by a search term.
     */
    public function paginate(string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->search($search)
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Build a batch query filtered by a free-text search term.
     *
     * Matches against batch number, warehouse location, notes, price,
     * quantity, weight, and the associated season's name.
     */
    public function search(string $term): Builder
    {
        $term = trim($term);

        return Batch::query()
            ->when($term !== '', function (Builder $query) use ($term): void {
                $query->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('batch_number', 'like', "%{$term}%")
                        ->orWhere('warehouse_location', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%")
                        ->orWhere('price', 'like', "%{$term}%")
                        ->orWhere('quantity', 'like', "%{$term}%")
                        ->orWhere('weight', 'like', "%{$term}%");
                });
            });
    }

    /**
     * Aggregate batch stats for the directory page.
     *
     * @return array<string, int|float>
     */
    public function stats(): array
    {
        return [
            'total_batches' => Batch::query()->count(),
            'received_batches' => Batch::query()->where('status', 'received')->count(),
            'total_weight' => (float) Batch::query()->sum('weight'),
        ];
    }

    /**
     * Load the relations required to render a batch profile.
     */
    public function loadProfileRelations(Batch $batch): Batch
    {
        return $batch->load(['compliances', 'ownerships', 'lotBatches.lot', 'user', 'batchFarmCollections.farmCollection.farm']);
    }

    /**
     * Create a batch from validated payload data.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, int $userId): Batch
    {
        return Batch::query()->create([
            'user_id' => $userId,
            'batch_number' => $this->generateBatchNumber(),
            'variety' => $validated['variety'] ?? null,
            'warehouse_location' => $validated['warehouse_location'],
            'quantity' => $validated['quantity_bags'],
            'weight' => $validated['net_weight_kg'],
            'price' => $validated['price'] ?? null,
            'currency' => $validated['currency'] ?? 'USD',
            'moisture_content' => $validated['moisture_content'] ?? null,
            'processing_date' => $validated['processing_date'] ?? null,
            'processing_method' => $validated['processing_method'] ?? null,
            'drying_method' => $validated['drying_method'] ?? null,
            'drying_duration' => $validated['drying_duration'] ?? null,
            'milling_status' => $validated['milling_status'] ?? null,
            'screen_size' => $validated['screen_size'] ?? null,
            'defect_count' => $validated['defect_count'] ?? null,
            'cup_score' => $validated['cup_score'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'received',
        ]);
    }

    /**
     * Generate a unique, human-readable batch number (e.g. BATCH-2026-AB12CD)
     * — used when the caller doesn't supply one on creation.
     */
    protected function generateBatchNumber(): string
    {
        do {
            $number = sprintf('BATCH-%d-%s', now()->year, strtoupper(Str::random(6)));
        } while (Batch::query()->where('batch_number', $number)->exists());

        return $number;
    }

    /**
     * Update a batch from validated payload data.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Batch $batch, array $validated): Batch
    {
        $batch->update([
            'batch_number' => $validated['batch_number'],
            'variety' => $validated['variety'],
            'warehouse_location' => $validated['warehouse_location'],
            'quantity' => $validated['quantity_bags'],
            'weight' => $validated['net_weight_kg'],
            'price' => $validated['price'],
            'currency' => $validated['currency'] ?? $batch->currency,
            'moisture_content' => $validated['moisture_content'] ?? null,
            'processing_date' => $validated['processing_date'],
            'processing_method' => $validated['processing_method'],
            'drying_method' => $validated['drying_method'],
            'drying_duration' => $validated['drying_duration'] ?? null,
            'milling_status' => $validated['milling_status'] ?? null,
            'screen_size' => $validated['screen_size'] ?? null,
            'defect_count' => $validated['defect_count'] ?? null,
            'cup_score' => $validated['cup_score'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $batch;
    }

    /**
     * Delete a batch.
     */
    public function destroy(Batch $batch): void
    {
        $batch->delete();
    }

    /**
     * Delete a single batch by id.
     */
    public function destroyById(int $id): void
    {
        Batch::query()->findOrFail($id)->delete();
    }

    /**
     * Create a compliance record for a batch.
     *
     * @param  array<string, mixed>  $validated
     */
    public function createCompliance(Batch $batch, int $userId, array $validated): BatchCompliance
    {
        return BatchCompliance::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $userId,
            ...$validated,
        ]);
    }

    /**
     * Link a farm collection to a batch, resolved by its collection_code,
     * via the batch_farm_collection pivot table. Records batch_id,
     * farm_collection_id, a denormalized copy of the collection's code,
     * the linking user, and a default "pending" status — every column the
     * pivot table has.
     */
    public function attachFarmCollection(Batch $batch, string $collectionCode, int $userId): BatchFarmCollection
    {
        $collection = FarmCollection::query()->where('collection_code', $collectionCode)->first();

        if (! $collection) {
            throw ValidationException::withMessages([
                'collection_code' => 'No farm collection with that code was found.',
            ]);
        }

        // A collection can only ever be used once, anywhere — once linked
        // to a batch its status flips to "batched" and every later attempt
        // (to this batch or any other) is rejected here, regardless of
        // which batch already claimed it.
        if ($collection->status !== 'pending') {
            throw ValidationException::withMessages([
                'collection_code' => "This farm collection has already been {$collection->status} and can't be used again.",
            ]);
        }

        $link = BatchFarmCollection::query()->create([
            'batch_id' => $batch->id,
            'farm_collection_id' => $collection->id,
            'farm_collection_code' => $collection->collection_code,
            'user_id' => $userId,
            'status' => 'pending',
        ]);

        $collection->update(['status' => 'batched']);

        return $link;
    }
}
