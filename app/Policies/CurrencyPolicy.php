<?php

namespace App\Policies;

use App\Models\Currency;
use App\Models\User;

class CurrencyPolicy
{
    /**
     * Every authenticated user may browse currencies to pick their own.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Every authenticated user may view a single currency.
     */
    public function view(User $user, Currency $currency): bool
    {
        return true;
    }

    /**
     * Only admins may create currencies.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins may update currencies.
     */
    public function update(User $user, Currency $currency): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins may delete currencies.
     */
    public function delete(User $user, Currency $currency): bool
    {
        return $user->isAdmin();
    }
}
