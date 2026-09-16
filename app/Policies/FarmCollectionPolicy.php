<?php

namespace App\Policies;

use App\Models\FarmCollection;
use App\Models\User;

class FarmCollectionPolicy
{
    /**
     * Determine whether the user can view the farm collection profile.
     *
     * Also allowed: the owner of a batch this collection is linked to (via
     * the batch_farm_collection pivot) — a batch's "Contributing Farm
     * Collections" list links out to each collection's profile regardless
     * of who recorded it.
     */
    public function view(User $user, FarmCollection $collection): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $collection->user_id === (int) $user->id) {
            return true;
        }

        return $collection->batchFarmCollections()
            ->whereHas('batch', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }

    /**
     * Determine whether the user can update the farm collection.
     */
    public function update(User $user, FarmCollection $collection): bool
    {
        return $user->isAdmin() || (int) $collection->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the farm collection.
     */
    public function delete(User $user, FarmCollection $collection): bool
    {
        return $user->isAdmin() || (int) $collection->user_id === (int) $user->id;
    }
}
