<?php

namespace App\Services;

use App\Models\CertificationMetadata;
use App\Models\CropVarietyMetadata;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FarmService
{
    public function __construct(private readonly FarmerService $farmers)
    {
    }

    /**
     * Get a base query builder for farms.
     */
    public function query(): Builder
    {
        return Farm::query();
    }

    /**
     * Get every farm, with its farmer, newest first.
     */
    public function list(): Collection
    {
        return $this->query()
            ->with('farmer')
            ->latest()
            ->get();
    }

    /**
     * Find a farm by its farm code — strictly by code, regardless of who
     * created it. Not an authorization check: a match here doesn't imply
     * the caller may act on the farm (e.g. record a collection against
     * it) — that's still enforced separately by FarmPolicy::update() when
     * the farm is actually used.
     */
    public function findByCode(string $farmCode): ?Farm
    {
        return $this->query()
            ->where('farm_code', $farmCode)
            ->first();
    }

    /**
     * Get every farm belonging to the given user's farmer profile, with
     * its farmer and harvest count, newest first.
     */
    public function listForUser(int $userId): Collection
    {
        return $this->query()
            ->whereHas('farmer', fn (Builder $query) => $query->where('user_id', $userId))
            ->with('farmer')
            ->withCount('harvests')
            ->latest()
            ->get();
    }

    /**
     * Get the active crop variety names available for a farm.
     */
    public function activeVarietyOptions(): Collection
    {
        return CropVarietyMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->values();
    }

    /**
     * Get every active crop variety (id, name, description) — used for the
     * farm-level "Varietals" multi-select, distinct from
     * activeVarietyOptions() which only feeds the free-text Harvest form.
     */
    public function activeVarietyMetadata(): Collection
    {
        return CropVarietyMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);
    }

    /**
     * Get every active certification (id, name, description) — used for
     * the farm-level "Quality & Compliance" multi-select.
     */
    public function activeCertificationOptions(): Collection
    {
        return CertificationMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'description']);
    }

    /**
     * Shape a farmer's details for the farm creation form.
     *
     * @return array<string, mixed>
     */
    public function farmerSummary(Farmer $farmer): array
    {
        return [
            'id' => $farmer->id,
            'first_name' => $farmer->first_name,
            'last_name' => $farmer->last_name,
            'district' => $farmer->district,
            'subcounty' => $farmer->subcounty,
            'tel' => $farmer->tel,
        ];
    }

    /**
     * Get the farmer record linked to a user, creating one from their
     * account details if they don't have one yet.
     */
    public function farmerForUser(User $user): Farmer
    {
        return Farmer::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'farmer_number' => $this->farmers->generateFarmerNumber(),
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'tel' => $user->telephone,
                'email' => $user->email,
                // The users table has no equivalent for this required
                // farmer column; the farmer can fill it in later.
                'district' => 'Not specified',
            ],
        );
    }

    /**
     * Register a new farmer on behalf of someone else (the person
     * submitting the farm isn't the farmer).
     *
     * @param  array<string, mixed>  $data
     */
    public function registerFarmer(array $data): Farmer
    {
        return $this->farmers->create($data);
    }

    /**
     * Create a new farm. `crop_variety_ids` / `certification_ids`, if
     * present, are synced onto the farm's many-to-many relations rather
     * than mass-assigned as plain columns.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Farm
    {
        $cropVarietyIds = $data['crop_variety_ids'] ?? null;
        $certificationIds = $data['certification_ids'] ?? null;
        unset($data['crop_variety_ids'], $data['certification_ids']);

        $farm = Farm::query()->create([
            ...$data,
            'farm_code' => $this->generateFarmCode(),
        ]);

        if ($cropVarietyIds !== null) {
            $farm->cropVarieties()->sync($cropVarietyIds);
        }
        if ($certificationIds !== null) {
            $farm->certifications()->sync($certificationIds);
        }

        return $farm;
    }

    /**
     * Generate a unique, human-readable farm code (e.g. FRM-2026-AB12CD) —
     * farms never accept one from the caller, so every farm gets one.
     */
    protected function generateFarmCode(): string
    {
        do {
            $code = sprintf('FRM-%d-%s', now()->year, strtoupper(Str::random(6)));
        } while (Farm::query()->where('farm_code', $code)->exists());

        return $code;
    }

    /**
     * Find a single farm by id, with its farmer and agronomy relations.
     */
    public function show(Farm $farm): Farm
    {
        $farm->load([
            'farmer',
            'soil',
            'climateZone',
            'cropVarieties',
            'certifications',
            'documents' => fn ($query) => $query->with('uploader')->latest(),
            'collections' => fn ($query) => $query->latest('collection_date'),
        ]);

        return $farm;
    }

    /**
     * Update an existing farm. `crop_variety_ids` / `certification_ids`,
     * if present, are synced onto the farm's many-to-many relations
     * rather than mass-assigned as plain columns.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Farm $farm, array $data): Farm
    {
        $cropVarietyIds = $data['crop_variety_ids'] ?? null;
        $certificationIds = $data['certification_ids'] ?? null;
        unset($data['crop_variety_ids'], $data['certification_ids']);

        $farm->update($data);

        if ($cropVarietyIds !== null) {
            $farm->cropVarieties()->sync($cropVarietyIds);
        }
        if ($certificationIds !== null) {
            $farm->certifications()->sync($certificationIds);
        }

        return $farm;
    }

    /**
     * Delete a farm.
     */
    public function destroy(Farm $farm): void
    {
        $farm->delete();
    }
}
