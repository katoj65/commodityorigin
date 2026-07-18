<?php

namespace App\Services;

use App\Models\Forecast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ForecastService
{
    /**
     * Get a base query builder for forecasts.
     */
    public function query(): Builder
    {
        return Forecast::query();
    }

    /**
     * Get every forecast, most recently effective first.
     *
     * @return Collection<int, Forecast>
     */
    public function all(): Collection
    {
        return Forecast::query()
            ->orderByDesc('effective_date')
            ->orderBy('category')
            ->get();
    }

    /**
     * Get the price-horizon forecasts (7-Day / 30-Day / 90-Day style entries).
     *
     * @return Collection<int, Forecast>
     */
    public function horizons(): Collection
    {
        return Forecast::query()
            ->whereNotNull('horizon')
            ->orderByRaw("FIELD(horizon, '7-Day', '30-Day', '90-Day')")
            ->get();
    }

    /**
     * Get the qualitative signal forecasts (harvest, supply, demand, etc).
     *
     * @return Collection<int, Forecast>
     */
    public function signals(): Collection
    {
        return Forecast::query()
            ->whereNull('horizon')
            ->orderByDesc('effective_date')
            ->get();
    }

    /**
     * Create a new forecast.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Forecast
    {
        return Forecast::query()->create($data)->refresh();
    }

    /**
     * Update an existing forecast.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Forecast $forecast, array $data): Forecast
    {
        $forecast->update($data);

        return $forecast->refresh();
    }

    /**
     * Delete a forecast.
     */
    public function destroy(Forecast $forecast): void
    {
        $forecast->delete();
    }
}
