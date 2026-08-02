<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Services\GoogleMapsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeocodeController extends Controller
{
    public function __construct(private readonly GoogleMapsService $maps)
    {
    }

    /**
     * Resolve a free-text address to coordinates.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->maps->geocode($validated['address']);

        if (! $result) {
            return response()->json(['message' => 'Could not resolve coordinates for that address.'], 422);
        }

        return response()->json($result);
    }
}
