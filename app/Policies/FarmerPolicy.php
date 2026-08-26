<?php

namespace App\Policies;

use App\Models\Farmer;
use App\Models\User;

class FarmerPolicy
{
    /**
     * Determine whether the user can view the farmer directory. Open to
     * every authenticated user.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a single farmer profile. Open to
     * every authenticated user.
     */
    public function view(User $user, Farmer $farmer): bool
    {
        return true;
    }

    /**
     * Determine whether the user can register a farmer profile. Open to
     * every authenticated user.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the farmer profile. Admin or
     * owner only.
     */
    public function update(User $user, Farmer $farmer): bool
    {
        return $user->isAdmin() || $farmer->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the farmer profile. Admin or
     * owner only.
     */
    public function delete(User $user, Farmer $farmer): bool
    {
        return $user->isAdmin() || $farmer->user_id === $user->id;
    }
}
