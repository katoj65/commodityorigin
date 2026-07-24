<?php

namespace App\Http\Controllers\Country;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CountryController extends Controller
{
    public function __construct(private readonly CountryService $countries)
    {
    }

    /**
     * Display the countries directory.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Country/CountriesPage', [
            'countries' => CountryResource::collection(
                $this->countries->all($request->string('search')->toString() ?: null, $request->string('region')->toString() ?: null),
            )->resolve(),
            'regions' => $this->countries->regions(),
            'filters' => $request->only(['search', 'region']),
        ]);
    }

    /**
     * Create a new country.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->countries->create($this->validateCountry($request));

        return back()->with('success', 'Country created successfully.');
    }

    /**
     * Update an existing country.
     */
    public function update(Request $request, Country $country): RedirectResponse
    {
        $this->countries->update($country, $this->validateCountry($request, $country));

        return back()->with('success', 'Country updated successfully.');
    }

    /**
     * Delete a country.
     */
    public function destroy(Country $country): RedirectResponse
    {
        $this->countries->destroy($country);

        return back()->with('success', 'Country deleted successfully.');
    }

    /**
     * Validate the create/update payload.
     *
     * @return array<string, mixed>
     */
    private function validateCountry(Request $request, ?Country $country = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('countries', 'name')->ignore($country)],
            'iso2' => ['required', 'string', 'size:2', 'uppercase', Rule::unique('countries', 'iso2')->ignore($country)],
            'iso3' => ['required', 'string', 'size:3', 'uppercase', Rule::unique('countries', 'iso3')->ignore($country)],
            'phone_code' => ['nullable', 'string', 'max:8'],
            'region' => ['nullable', 'string', 'max:255'],
            'subregion' => ['nullable', 'string', 'max:255'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'currency_name' => ['nullable', 'string', 'max:255'],
            'is_coffee_producer' => ['nullable', 'boolean'],
            'coffee_production_bags' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
