<?php

namespace App\Policies;

use App\Models\Market;
use App\Models\User;

class MarketPolicy
{
    /**
     * Only the seller who listed the lot may edit it or manage its photos.
     */
    public function update(User $user, Market $market): bool
    {
        return $market->user_id === $user->id;
    }

    /**
     * Only the seller who listed the lot may delete it.
     */
    public function delete(User $user, Market $market): bool
    {
        return $market->user_id === $user->id;
    }
}
