<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\FarmCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FarmCollectionService
{
    /**
     * Get a base query builder for farm collections.
     */
    public function query(): Builder
    {
        return FarmCollection::query();
    }

    /**
     * Get every collection recorded against the given farm, most recent
     * collection date first.
     */
    public function listForFarm(Farm $farm): Collection
    {
        return $this->query()
            ->where('farm_id', $farm->id)
            ->latest('collection_date')
            ->latest('id')
            ->get();
    }

    /**
     * Create a new farm collection record. A collection_code is always
     * server-generated — never accepted from the caller.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): FarmCollection
    {
        return FarmCollection::query()->create([
            ...$data,
            'collection_code' => $this->generateCollectionCode(),
        ]);
    }

    /**
     * Generate a unique, human-readable collection code (e.g.
     * COL-2026-AB12CD) — mirrors FarmService::generateFarmCode() /
     * BatchService::generateBatchNumber() exactly.
     */
    protected function generateCollectionCode(): string
    {
        do {
            $code = sprintf('COL-%d-%s', now()->year, strtoupper(Str::random(6)));
        } while (FarmCollection::query()->where('collection_code', $code)->exists());

        return $code;
    }

    /**
     * Update an existing farm collection record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(FarmCollection $collection, array $data): FarmCollection
    {
        $collection->update($data);

        return $collection;
    }

    /**
     * Delete a farm collection record.
     */
    public function delete(FarmCollection $collection): void
    {
        $collection->delete();
    }

    /**
     * Unit options for the collection form.
     *
     * @return array<int, string>
     */
    public function unitOptions(): array
    {
        return [
            'kg',
            'lbs',
            'bags',
        ];
    }

    /**
     * Payment status options for the collection form.
     *
     * @return array<int, string>
     */
    public function paymentStatusOptions(): array
    {
        return [
            'pending',
            'partial',
            'paid',
            'cancelled',
        ];
    }
}
