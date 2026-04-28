<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\Lot;
use App\Models\User;

class LotPolicy
{
    /**
     * Determine whether the user can create a lot.
     */
    public function create(User $user, ?Batch $batch = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $batch) {
            return false;
        }

        return (int) $batch->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can view the lot.
     */
    public function view(User $user, Lot $lot): bool
    {
        return $this->ownsOrAdmins($user, $lot);
    }

    /**
     * Determine whether the user can update the lot.
     */
    public function update(User $user, Lot $lot): bool
    {
        return $this->ownsOrAdmins($user, $lot);
    }

    /**
     * Determine whether the user can delete the lot.
     */
    public function delete(User $user, Lot $lot): bool
    {
        return $this->ownsOrAdmins($user, $lot);
    }

    /**
     * Determine whether the user owns the lot or can manage it as an admin.
     */
    private function ownsOrAdmins(User $user, Lot $lot): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $lot->user_id === (int) $user->id) {
            return true;
        }

        return (int) ($lot->batch?->user_id ?? 0) === (int) $user->id;
    }
}
