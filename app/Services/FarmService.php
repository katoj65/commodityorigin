<?php

namespace App\Services;

use App\Models\CertificationMetadata;
use App\Models\CropVarietyMetadata;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FarmService
{
    /**
     * Get a base query builder for farms.
     */
    public function query(): Builder
    {
        return Farm::query();
    }

    /**
     * Get every farm, with its owning user, newest first.
     */
    public function list(): Collection
    {
        return $this->query()
            ->with('user')
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
     * Get every farm owned by the given user, newest first.
     */
    public function listForUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
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
     * farm-level "Varietals" multi-select, distinct from the plain name
     * list returned by activeVarietyOptions().
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
     * Find a single farm by id, with its owning user and agronomy relations.
     */
    public function show(Farm $farm): Farm
    {
        $farm->load([
            'user',
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
