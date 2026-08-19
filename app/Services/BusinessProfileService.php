<?php

namespace App\Services;

use App\Models\BusinessProfile;
use App\Models\BusinessType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BusinessProfileService
{
    /**
     * Get a base query builder for business profiles.
     */
    public function query(): Builder
    {
        return BusinessProfile::query();
    }

    /**
     * Business types a registering business can pick from, spanning the
     * coffee supply chain — used both for validating business_type and
     * for populating the business profile form's options.
     *
     * @return Collection<int, string>
     */
    public function businessTypeOptions(): Collection
    {
        return BusinessType::query()
            ->active()
            ->orderBy('sort_order')
            ->pluck('name');
    }

    /**
     * Get a user's business profile, if they have one.
     */
    public function forUser(int $userId): ?BusinessProfile
    {
        return BusinessProfile::where('user_id', $userId)->first();
    }

    /**
     * Create a new business profile for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): BusinessProfile
    {
        return BusinessProfile::create([...$data, 'user_id' => $user->id]);
    }

    /**
     * Update an existing business profile.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(BusinessProfile $businessProfile, array $data): BusinessProfile
    {
        $businessProfile->update($data);

        return $businessProfile;
    }

    /**
     * Create or update a user's business profile in one call — used by the
     * profile form, which doesn't need to know whether one already exists.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(User $user, array $data): BusinessProfile
    {
        return BusinessProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            $data,
        );
    }

    /**
     * Delete a user's business profile.
     */
    public function destroy(BusinessProfile $businessProfile): void
    {
        $businessProfile->delete();
    }
}
