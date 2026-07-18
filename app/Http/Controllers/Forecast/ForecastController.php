<?php

namespace App\Http\Controllers\Forecast;

use App\Http\Controllers\Controller;
use App\Http\Resources\ForecastResource;
use App\Models\Forecast;
use App\Services\ForecastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ForecastController extends Controller
{
    public function __construct(private readonly ForecastService $forecasts)
    {
    }

    /**
     * Display the market forecast page.
     */
    public function index(): Response
    {
        return Inertia::render('Forecast/ForecastPage', [
            'horizons' => ForecastResource::collection($this->forecasts->horizons())->resolve(),
            'signals' => ForecastResource::collection($this->forecasts->signals())->resolve(),
        ]);
    }

    /**
     * Create a new forecast.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->forecasts->create($this->validateForecast($request));

        return back()->with('success', 'Forecast created successfully.');
    }

    /**
     * Update an existing forecast.
     */
    public function update(Request $request, Forecast $forecast): RedirectResponse
    {
        $this->forecasts->update($forecast, $this->validateForecast($request));

        return back()->with('success', 'Forecast updated successfully.');
    }

    /**
     * Delete a forecast.
     */
    public function destroy(Forecast $forecast): RedirectResponse
    {
        $this->forecasts->destroy($forecast);

        return back()->with('success', 'Forecast deleted successfully.');
    }

    /**
     * Validate the create/update payload.
     *
     * @return array<string, mixed>
     */
    private function validateForecast(Request $request): array
    {
        return $request->validate([
            'crop_type' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'horizon' => ['nullable', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'detail' => ['nullable', 'string', 'max:2000'],
            'direction' => ['nullable', Rule::in(['up', 'down', 'steady'])],
            'confidence' => ['nullable', 'integer', 'min:0', 'max:100'],
            'effective_date' => ['nullable', 'date'],
        ]);
    }
}
