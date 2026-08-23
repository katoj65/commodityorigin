<?php

namespace App\Services;

use App\Models\Farmer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class FarmerService
{
    /**
     * Get every farmer with their farm count, newest first.
     *
     * @return Collection<int, Farmer>
     */
    public function all(): Collection
    {
        return Farmer::query()->with('cooperative')->withCount('farms')->latest()->get();
    }

    /**
     * Register a new farmer. A farmer_number is generated automatically
     * when the caller didn't already supply one.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Farmer
    {
        $data['farmer_number'] ??= $this->generateFarmerNumber();

        return Farmer::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Farmer $farmer, array $data): Farmer
    {
        $farmer->update($data);

        return $farmer;
    }

    public function delete(Farmer $farmer): void
    {
        $farmer->delete();
    }

    /**
     * FMR-2026-A1B2C3 — same do-while uniqueness pattern OrderService
     * uses for order_number.
     */
    public function generateFarmerNumber(): string
    {
        do {
            $candidate = 'FMR-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Farmer::query()->where('farmer_number', $candidate)->exists());

        return $candidate;
    }
}
