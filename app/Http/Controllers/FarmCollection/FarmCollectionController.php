<?php

namespace App\Http\Controllers\FarmCollection;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmCollectionActivityResource;
use App\Http\Resources\FarmCollectionResource;
use App\Models\CropVarietyMetadata;
use App\Models\Currency;
use App\Models\FarmCollection;
use App\Models\FarmCollectionActivity;
use App\Models\FarmCollectionActivityMetadata;
use App\Models\SeasonMetadata;
use App\Services\FarmCollectionActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FarmCollectionController extends Controller
{
    public function __construct(private readonly FarmCollectionActivityService $activities)
    {
    }

    /**
     * Display a single farm collection's details.
     */
    public function show(FarmCollection $collection): Response
    {
        Gate::authorize('view', $collection);

        $collection->load(['farm', 'user']);

        return Inertia::render('FarmCollection/FarmCollectionProfile', [
            'collection' => FarmCollectionResource::make($collection)->resolve(),
            'coffeeTypeOptions' => CropVarietyMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'harvestSeasonOptions' => SeasonMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'currencyOptions' => Currency::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->pluck('code'),
            'activities' => FarmCollectionActivityResource::collection($this->activities->forCollection($collection))->resolve(),
            'activityOptions' => FarmCollectionActivityMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'name'])
                ->map(fn (FarmCollectionActivityMetadata $option): array => [
                    'slug' => $option->slug,
                    'name' => $option->name,
                ]),
        ]);
    }

    /**
     * Record a manual activity-log entry for this farm collection —
     * `event` must be an active slug in farm_collection_activity_metadata.
     */
    public function storeActivity(Request $request, FarmCollection $collection): RedirectResponse
    {
        Gate::authorize('update', $collection);

        $validated = $request->validate([
            'event' => [
                'required',
                'string',
                Rule::exists('farm_collection_activity_metadata', 'slug')->where('is_active', true),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->activities->record($collection, $validated['event'], $validated['description'] ?? null, $request->user()->id);

        return back()->with('success', 'Activity recorded.');
    }

    /**
     * Remove one activity-log entry from this farm collection.
     */
    public function destroyActivity(FarmCollection $collection, FarmCollectionActivity $activity): RedirectResponse
    {
        Gate::authorize('update', $collection);
        abort_unless((int) $activity->farm_collection_id === (int) $collection->id, 404);

        $this->activities->delete($activity);

        return back()->with('success', 'Activity removed.');
    }
}
