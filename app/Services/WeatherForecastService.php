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

    /**
     * Match a farm's free-text location to the closest known forecast
     * region by substring, falling back to the first region on file
     * (by sort_order) when nothing matches — so a farm profile always
     * has a reasonable regional outlook to show, clearly labelled.
     */
    public function matchRegionFor(?string $location): ?string
    {
        $regions = $this->activeRegions();

        if ($regions->isEmpty()) {
            return null;
        }

        $needle = strtolower(trim((string) $location));

        if ($needle !== '') {
            foreach ($regions as $region) {
                $shortName = strtolower(trim(explode('(', $region)[0]));

                if ($shortName !== '' && str_contains($needle, $shortName)) {
                    return $region;
                }
            }
        }

        return $regions->first();
    }

    /**
     * Get the monthly planting-season outlook for a region — the
     * first-of-month forecast entries seeded for the months ahead,
     * as distinct from the near-term daily forecasts on the weather
     * directory page.
     */
    public function monthlyOutlookForRegion(string $region, int $months = 6): Collection
    {
        return $this->query()
            ->where('region', $region)
            ->where('is_active', true)
            ->where('forecast_date', '>=', now()->startOfMonth())
            ->whereDay('forecast_date', 1)
            ->orderBy('forecast_date')
            ->limit($months)
            ->get();
    }
}
