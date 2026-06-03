<?php

namespace App\Policies;

use App\Models\LotRequest;
use App\Models\User;

class LotRequestPolicy
{
    public function update(User $user, LotRequest $lotRequest): bool
    {
        return $this->ownsOrAdmins($user, $lotRequest);
    }

    public function delete(User $user, LotRequest $lotRequest): bool
    {
        return $this->ownsOrAdmins($user, $lotRequest);
    }

    private function ownsOrAdmins(User $user, LotRequest $lotRequest): bool
    {
        return $user->isAdmin() || (int) $lotRequest->user_id === (int) $user->id;
    }
}
