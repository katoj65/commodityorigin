<?php

namespace App\Services;

use App\Models\CropVarietyMetadata;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\User;
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
     * Get every farm created by the given user, with its farmer and
     * harvest count, newest first.
     */
    public function listForUser(int $userId): Collection
    {
        return $this->query()
            ->where('created_by_user_id', $userId)
            ->with('farmer')
            ->withCount('harvests')
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
     * Get the farmer record linked to a user, creating one from their
     * account details if they don't have one yet.
     */
    public function farmerForUser(User $user): Farmer
    {
        return Farmer::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'telephone' => $user->telephone,
                'email' => $user->email,
                // The users table has no equivalent for these required
                // farmer columns; the farmer can fill them in later.
                'district' => 'Not specified',
                'coffee_type' => 'Not specified',
            ],
        );
    }

    /**
     * Register a new farmer on behalf of someone else (the person
     * submitting the farm isn't the farmer).
     *
     * @param  array<string, mixed>  $data
     */
    public function registerFarmer(array $data): Farmer
    {
        return Farmer::query()->create($data);
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
        $farm->load(['farmer', 'harvests' => fn ($query) => $query->latest('harvest_date')]);

        return $farm;
    }

    /**
     * Update an existing farm.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Farm $farm, array $data): Farm
    {
        $farm->update($data);

        return $farm;
    }

    /**
     * Delete a farm.
     */
    public function destroy(Farm $farm): void
    {
        $farm->delete();
    }
}
