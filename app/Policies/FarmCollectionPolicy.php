<?php

namespace App\Policies;

use App\Models\FarmCollection;
use App\Models\User;

class FarmCollectionPolicy
{
    /**
     * Determine whether the user can view the farm collection profile.
     */
    public function view(User $user, FarmCollection $collection): bool
    {
        return $user->isAdmin() || (int) $collection->user_id === (int) $user->id;
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
