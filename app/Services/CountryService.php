<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CountryService
{
    /**
     * Get a base query builder for countries.
     */
    public function query(): Builder
    {
        return Country::query();
    }

    /**
     * Get every country, alphabetically ordered, optionally filtered by a
     * search term and/or region.
     *
     * @return Collection<int, Country>
     */
    public function all(?string $search = null, ?string $region = null): Collection
    {
        return Country::query()
            ->when($search, fn (Builder $query) => $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('iso2', 'like', "%{$search}%")
                    ->orWhere('iso3', 'like', "%{$search}%");
            }))
            ->when($region, fn (Builder $query) => $query->where('region', $region))
            ->orderBy('name')
            ->get();
    }

    /**
     * Get the distinct list of regions, alphabetically ordered.
     *
     * @return array<int, string>
     */
    public function regions(): array
    {
        return Country::query()
            ->whereNotNull('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region')
            ->all();
    }

    /**
     * Find a country by its ISO2 code.
     */
    public function findByIso2(string $iso2): ?Country
    {
        return Country::query()->where('iso2', strtoupper($iso2))->first();
    }

    /**
     * Create a new country.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Country
    {
        return Country::query()->create($data)->refresh();
    }

    /**
     * Update an existing country.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Country $country, array $data): Country
    {
        $country->update($data);

        return $country->refresh();
    }

    /**
     * Delete a country.
     */
    public function destroy(Country $country): void
    {
        $country->delete();
    }

    /**
     * Get every coffee-growing country, ranked by annual production volume
     * (60kg bags), highest first.
     *
     * @return Collection<int, Country>
     */
    public function coffeeProducers(): Collection
    {
        return Country::query()
            ->where('is_coffee_producer', true)
            ->orderByDesc('coffee_production_bags')
            ->get();
    }

    /**
     * Compare coffee production across a set of coffee-growing countries.
     * Non-producer countries and unrecognized codes are silently excluded.
     * With no codes given, every coffee-growing country is returned.
     *
     * @param  array<int, string>  $iso2Codes
     * @return Collection<int, Country>
     */
    public function compareCoffeeProduction(array $iso2Codes = []): Collection
    {
        $iso2Codes = array_map('strtoupper', $iso2Codes);

        return Country::query()
            ->where('is_coffee_producer', true)
            ->when($iso2Codes, fn (Builder $query) => $query->whereIn('iso2', $iso2Codes))
            ->orderByDesc('coffee_production_bags')
            ->get();
    }
}
