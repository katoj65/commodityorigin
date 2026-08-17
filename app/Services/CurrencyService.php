<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CurrencyService
{
    /**
     * Get every currency, in display order — used by the admin management
     * page, which needs to see inactive currencies too.
     *
     * @return Collection<int, Currency>
     */
    public function all(): Collection
    {
        return Currency::query()->orderBy('sort_order')->orderBy('code')->get();
    }

    /**
     * Get only active currencies, in display order — used anywhere a user
     * picks a settlement currency.
     *
     * @return Collection<int, Currency>
     */
    public function active(): Collection
    {
        return Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();
    }

    /**
     * Create a new currency.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Currency
    {
        return Currency::query()->create($data);
    }

    /**
     * Update an existing currency.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Currency $currency, array $data): Currency
    {
        $currency->update($data);

        return $currency;
    }

    /**
     * Delete a currency. Any user who had it selected keeps their record
     * but reverts to no preferred currency, via the nullOnDelete FK.
     */
    public function delete(Currency $currency): void
    {
        $currency->delete();
    }

    /**
     * Set the authenticated user's preferred settlement currency.
     */
    public function setUserCurrency(User $user, string $code): User
    {
        $user->forceFill(['currency_code' => $code])->save();

        return $user;
    }
}
