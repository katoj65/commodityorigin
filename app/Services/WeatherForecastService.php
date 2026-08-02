<?php

namespace App\Services;

use App\Models\WeatherForecast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WeatherForecastService
{
    /**
     * Get a base query builder for weather forecasts.
     */
    public function query(): Builder
    {
        return WeatherForecast::query();
    }

    /**
     * Get every active forecast, ordered for display.
     */
    public function active(): Collection
    {
        return $this->query()->active()->get();
    }

    /**
     * Get the distinct active regions, for filtering.
     */
    public function activeRegions(): Collection
    {
        return $this->active()->pluck('region')->unique()->values();
    }
}
