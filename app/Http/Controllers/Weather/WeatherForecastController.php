<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use App\Http\Resources\WeatherForecastResource;
use App\Services\WeatherForecastService;
use Inertia\Inertia;
use Inertia\Response;

class WeatherForecastController extends Controller
{
    public function __construct(private readonly WeatherForecastService $weather)
    {
    }

    /**
     * Display the coffee-region weather forecast directory.
     */
    public function index(): Response
    {
        return Inertia::render('Farm/Weather', [
            'forecasts' => WeatherForecastResource::collection($this->weather->active())->resolve(),
            'regionOptions' => $this->weather->activeRegions(),
        ]);
    }
}
