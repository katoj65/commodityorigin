<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    /**
     * Determine whether the user can view the batch profile.
     *
     * Also allowed: the owner of a lot this batch is linked to (via the
     * lot_batch pivot) — a lot's traceability chain links out to its
     * source batch's profile regardless of who recorded the batch.
     */
    public function view(User $user, Batch $batch): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $batch->user_id === (int) $user->id) {
            return true;
        }

        return $batch->lotBatches()
            ->whereHas('lot', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }

    /**
     * Determine whether the user can update batch-owned records.
     */
    public function update(User $user, Batch $batch): bool
    {
        return $user->isAdmin() || (int) $batch->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the batch.
     */
    public function delete(User $user, Batch $batch): bool
    {
        return $user->isAdmin() || (int) $batch->user_id === (int) $user->id;
    }
}
