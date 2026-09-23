<?php

namespace App\Services;

use App\Models\SettingsRfqSpecification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SettingsRfqSpecificationService
{
    /**
     * Get a base query builder for RFQ specifications.
     */
    public function query(): Builder
    {
        return SettingsRfqSpecification::query();
    }

    /**
     * Get every RFQ specification owned by a given user, newest first.
     */
    public function forUser(int $userId): Collection
    {
        return $this->query()->where('user_id', $userId)->latest()->get();
    }

    /**
     * Store a new RFQ specification for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $userId): SettingsRfqSpecification
    {
        return SettingsRfqSpecification::create([...$data, 'user_id' => $userId]);
    }

    /**
     * Update an existing RFQ specification.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SettingsRfqSpecification $specification, array $data): SettingsRfqSpecification
    {
        $specification->update($data);

        return $specification;
    }

    /**
     * Delete an RFQ specification.
     */
    public function delete(SettingsRfqSpecification $specification): void
    {
        $specification->delete();
    }
}
