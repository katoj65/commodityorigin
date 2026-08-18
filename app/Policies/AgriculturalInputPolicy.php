<?php

namespace App\Policies;

use App\Models\AgriculturalInput;
use App\Models\User;

class AgriculturalInputPolicy
{
    /**
     * Every authenticated user may browse the input store.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Every authenticated user may view a single input.
     */
    public function view(User $user, AgriculturalInput $agriculturalInput): bool
    {
        return true;
    }

    /**
     * Only admins may add new inputs to the store.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins may edit an input's listing.
     */
    public function update(User $user, AgriculturalInput $agriculturalInput): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins may remove an input from the store.
     */
    public function delete(User $user, AgriculturalInput $agriculturalInput): bool
    {
        return $user->isAdmin();
    }
}
