<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    private const GEOCODE_ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json';

    private readonly ?string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('services.google_maps.key');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    /**
     * Resolve a free-text address to coordinates via Google's Geocoding API.
     *
     * @return array{lat: float, lng: float, formatted_address: string}|null
     */
    public function geocode(string $address): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::get(self::GEOCODE_ENDPOINT, [
            'address' => $address,
            'key' => $this->apiKey,
        ]);

        if (! $response->ok()) {
            Log::warning('Google Maps geocode request failed', ['status' => $response->status()]);

            return null;
        }

        $body = $response->json();

        if (($body['status'] ?? null) !== 'OK' || empty($body['results'][0])) {
            return null;
        }

        $result = $body['results'][0];
        $location = $result['geometry']['location'];

        return [
            'lat' => (float) $location['lat'],
            'lng' => (float) $location['lng'],
            'formatted_address' => $result['formatted_address'],
        ];
    }
}
