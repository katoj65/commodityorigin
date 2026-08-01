<?php

namespace App\Services;

use App\Models\CropVarietyMetadata;
use App\Models\Farm;
use App\Models\Farmer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FarmService
{
    /**
     * Get a base query builder for farms.
     */
    public function query(): Builder
    {
        return Farm::query();
    }

    /**
     * Get every farm, with its farmer, newest first.
     */
    public function list(): Collection
    {
        return $this->query()
            ->with('farmer')
            ->latest()
            ->get();
    }

    /**
     * Get the active crop variety names available for a farm.
     */
    public function activeVarietyOptions(): Collection
    {
        return CropVarietyMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->values();
    }

    /**
     * Shape a farmer's details for the farm creation form.
     *
     * @return array<string, mixed>
     */
    public function farmerSummary(Farmer $farmer): array
    {
        return [
            'id' => $farmer->id,
            'first_name' => $farmer->first_name,
            'last_name' => $farmer->last_name,
            'district' => $farmer->district,
            'sub_county' => $farmer->sub_county,
            'coffee_type' => $farmer->coffee_type,
            'cooperative' => $farmer->cooperative,
        ];
    }

    /**
     * Get every farmer as a lightweight option list, for the farm creation
     * form's farmer picker when no farmer is pre-selected.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function farmerOptions(): array
    {
        return Farmer::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn (Farmer $farmer): array => [
                'id' => $farmer->id,
                'name' => trim("{$farmer->first_name} {$farmer->last_name}") ?: "Farmer #{$farmer->id}",
            ])
            ->all();
    }

    /**
     * Create a new farm.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Farm
    {
        return Farm::query()->create($data);
    }

    /**
     * Find a single farm by id, with its farmer.
     */
    public function show(Farm $farm): Farm
    {
        $farm->load('farmer');

        return $farm;
    }
}
