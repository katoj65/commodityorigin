<?php

namespace App\Http\Controllers\FarmCollection;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmCollectionActivityResource;
use App\Http\Resources\FarmCollectionResource;
use App\Http\Resources\FarmSustainabilityPracticeResource;
use App\Http\Resources\UserFarmOwnershipResource;
use App\Models\BatchFarmCollection;
use App\Models\CropVarietyMetadata;
use App\Models\Currency;
use App\Models\FarmCollection;
use App\Models\FarmCollectionActivity;
use App\Models\FarmCollectionActivityMetadata;
use App\Models\LotBatch;
use App\Models\SeasonMetadata;
use App\Models\SustainabilityPracticesMetadata;
use App\Models\UserFarmOwnership;
use App\Services\FarmCollectionActivityService;
use App\Services\FarmSustainabilityPracticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FarmCollectionController extends Controller
{
    public function __construct(
        private readonly FarmCollectionActivityService $activities,
        private readonly FarmSustainabilityPracticeService $sustainabilityPractices,
    ) {
    }

    /**
     * Display a single farm collection's details.
     */
    public function show(FarmCollection $collection): Response
    {
        Gate::authorize('view', $collection);

        $collection->load(['farm.certifications', 'user']);

        $farmOwner = $collection->farm_id
            ? UserFarmOwnership::query()
                ->where('farm_id', $collection->farm_id)
                ->with('user')
                ->orderByDesc('is_primary')
                ->orderByDesc('created_at')
                ->first()
            : null;

        return Inertia::render('FarmCollection/FarmCollectionProfile', [
            'collection' => FarmCollectionResource::make($collection)->resolve(),
            'custodyChain' => $this->custodyChain($collection),
            'farmOwner' => $farmOwner ? UserFarmOwnershipResource::make($farmOwner)->resolve() : null,
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
            'sustainabilityPractices' => FarmSustainabilityPracticeResource::collection(
                $collection->farm ? $this->sustainabilityPractices->forFarm($collection->farm) : []
            )->resolve(),
            'sustainabilityPracticeOptions' => SustainabilityPracticesMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'name'])
                ->map(fn (SustainabilityPracticesMetadata $option): array => [
                    'slug' => $option->slug,
                    'name' => $option->name,
                ]),
        ]);
    }

    /**
     * Trace this collection forward through the real custody chain —
     * batch (via batch_farm_collection), lot (via lot_batch), and
     * blockchain commit — so the profile's lineage strip only shows
     * stages this specific collection has actually reached. A step
     * that hasn't happened yet is omitted rather than filled with a
     * placeholder code.
     */
    private function custodyChain(FarmCollection $collection): array
    {
        $link = BatchFarmCollection::query()
            ->where('farm_collection_id', $collection->id)
            ->with('batch')
            ->first();
        $batch = $link?->batch;

        $lotLink = $batch
            ? LotBatch::query()->where('batch_id', $batch->id)->with('lot.blockchain')->first()
            : null;
        $lot = $lotLink?->lot;

        /* ── Only shown when it's a sane fraction of the batch (0-100%) —
           a batch's recorded weight doesn't always reconcile with the
           sum of its linked collections, and a ">100%" figure would
           read as broken rather than communicate anything real. ────── */
        $contributionPct = null;
        if ($batch && $collection->unit === 'kg' && (float) $batch->weight > 0) {
            $pct = round((float) $collection->quantity / (float) $batch->weight * 100, 1);
            $contributionPct = ($pct > 0 && $pct <= 100) ? $pct : null;
        }

        return [
            'batch' => $batch ? [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'weight' => (float) $batch->weight,
                'status' => $batch->status,
                'contribution_pct' => $contributionPct,
            ] : null,
            'lot' => $lot ? [
                'id' => $lot->id,
                'lot_number' => $lot->lot_number,
                'lot_name' => $lot->lot_name,
                'net_weight_kg' => $lot->net_weight_kg !== null ? (float) $lot->net_weight_kg : null,
                'grade' => $lot->grade,
                'status' => $lot->status,
            ] : null,
            'tokenised' => ($lot && $lot->blockchain) ? [
                'hash' => $lot->blockchain->hash,
                'network' => $lot->blockchain->network,
                'committed_at' => optional($lot->blockchain->committed_at)?->toDateTimeString(),
            ] : null,
        ];
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
