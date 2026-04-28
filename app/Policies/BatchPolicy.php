<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    /**
     * Determine whether the user can view the batch profile.
     */
    public function view(User $user, Batch $batch): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $batch->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can update batch-owned records.
     */
    public function update(User $user, Batch $batch): bool
    {
        return (int) $batch->user_id === (int) $user->id;
    }
}
