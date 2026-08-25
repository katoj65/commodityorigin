<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\Lot;
use App\Models\User;

class LotPolicy
{
    /**
     * Determine whether the user can create a lot.
     *
     * When authorizing against a specific batch (the batch-scoped creation
     * flow), the batch must be owned by the user. Otherwise — the
     * standalone lot creation form, where a batch is linked afterward via
     * the lot_batch pivot rather than at creation time — any authenticated
     * user may create a lot, since the role:farmer,admin,buyer route
     * middleware already restricts who reaches it.
     */
    public function create(User $user, ?Batch $batch = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($batch) {
            return (int) $batch->user_id === (int) $user->id;
        }

        return true;
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

        return (int) $lot->user_id === (int) $user->id;
    }
}
