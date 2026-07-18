<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ExchangeRateService
{
    /**
     * Get a base query builder for exchange rates.
     */
    public function query(): Builder
    {
        return ExchangeRate::query();
    }

    /**
     * Get every exchange rate, ordered by base then quote currency.
     *
     * @return Collection<int, ExchangeRate>
     */
    public function all(): Collection
    {
        return ExchangeRate::query()
            ->orderBy('base_currency')
            ->orderBy('quote_currency')
            ->get();
    }

    /**
     * Find the rate for a specific currency pair.
     */
    public function forPair(string $baseCurrency, string $quoteCurrency): ?ExchangeRate
    {
        return ExchangeRate::query()
            ->where('base_currency', $baseCurrency)
            ->where('quote_currency', $quoteCurrency)
            ->first();
    }

    /**
     * Create or update the rate for a currency pair.
     *
     * @param  array<string, mixed>  $data
     */
    public function upsertRate(string $baseCurrency, string $quoteCurrency, array $data): ExchangeRate
    {
        return ExchangeRate::query()->updateOrCreate(
            ['base_currency' => $baseCurrency, 'quote_currency' => $quoteCurrency],
            [
                ...$data,
                'recorded_at' => $data['recorded_at'] ?? now(),
            ],
        )->refresh();
    }

    /**
     * Delete a currency pair's rate.
     */
    public function destroy(ExchangeRate $rate): void
    {
        $rate->delete();
    }
}
