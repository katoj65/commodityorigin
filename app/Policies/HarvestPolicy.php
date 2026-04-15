<?php

namespace App\Policies;

use App\Models\Harvest;
use App\Models\User;

class HarvestPolicy
{
    /**
     * Determine whether the user can create harvest records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the harvest profile.
     */
    public function view(User $user, Harvest $harvest): bool
    {
        if ($user->isAdmin() || $user->hasAnyRole(['auditor', 'regulator'])) {
            return true;
        }

        if ((int) $harvest->user_id === (int) $user->id) {
            return true;
        }

        return (int) ($harvest->farm?->farmer?->user_id ?? 0) === (int) $user->id;
    }

    /**
     * Determine whether the user can update the harvest record.
     */
    public function update(User $user, Harvest $harvest): bool
    {
        return (int) $harvest->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the harvest record.
     */
    public function delete(User $user, Harvest $harvest): bool
    {
        return (int) $harvest->user_id === (int) $user->id;
    }
}
