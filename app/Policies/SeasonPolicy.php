<?php

namespace App\Policies;

use App\Models\Season;
use App\Models\User;

class SeasonPolicy
{
    /**
     * Determine whether the user can view the season profile.
     */
    public function view(User $user, Season $season): bool
    {
        return (int) $season->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can update the season record.
     */
    public function update(User $user, Season $season): bool
    {
        return (int) $season->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the season record.
     */
    public function delete(User $user, Season $season): bool
    {
        return (int) $season->user_id === (int) $user->id;
    }
}
