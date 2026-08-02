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
     * Determine whether the user can view their harvest list. Every
     * authenticated user may view their own recorded harvests; the
     * controller scopes the query to "own" — admins see everyone's.
     */
    public function viewAny(User $user): bool
    {
        return true;
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
     * Creator or admin only.
     */
    public function update(User $user, Harvest $harvest): bool
    {
        return $user->isAdmin() || (int) $harvest->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the harvest record.
     * Creator or admin only.
     */
    public function delete(User $user, Harvest $harvest): bool
    {
        return $user->isAdmin() || (int) $harvest->user_id === (int) $user->id;
    }
}
