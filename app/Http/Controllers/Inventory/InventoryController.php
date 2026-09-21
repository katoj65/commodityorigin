<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmCollectionResource;
use App\Models\AcidityMetadata;
use App\Models\AftertasteMetadata;
use App\Models\AromaMetadata;
use App\Models\Batch;
use App\Models\BodyMetadata;
use App\Models\Currency;
use App\Models\CropVarietyMetadata;
use App\Models\DryingMethodMetadata;
use App\Models\FarmCollection;
use App\Models\FlavorMetadata;
use App\Models\Lot;
use App\Models\MillingMetadata;
use App\Models\ProcessingMetadata;
use App\Models\SeasonMetadata;
use App\Services\CoffeeGradeService;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(
        private readonly CoffeeGradeService $coffeeGrades,
        private readonly CountryService $countries,
    ) {
    }

    /**
     * Display the Inventory page. The "Registered Smallholder Deliveries"
     * table is real farm collection data for the authenticated user;
     * every other stat/card on the page is still illustrative dummy data.
     */
    public function index(Request $request): Response
    {
        $farmCollections = FarmCollection::query()
            ->where('user_id', $request->user()->id)
            ->with('farm')
            ->latest('collection_date')
            ->get();

        return Inertia::render('Inventory/Inventory', [
            ...$this->modalOptions(),
            'farmCollections' => FarmCollectionResource::collection($farmCollections)->resolve(),
            'lifecycleStats' => $this->lifecycleStats($request->user()->id),
        ]);
    }

    /**
     * Real per-stage totals for the "Physical Inventory Lifecycle" KPI
     * cards — a count and a kg total for each of the four stages, scoped
     * to the authenticated user. The frontend falls back to the card's
     * own dummy figure wherever a stage's count is 0, rather than
     * showing a bare "0 MT" for a user with no real records yet.
     *
     * Tokenised is deliberately NOT "lots with a truthy status flag" —
     * it's real lots that have an actual row in `blockchains`, the same
     * definition StoreController::stageSummary() uses, joined rather
     * than summed in PHP so the figure is computed straight from the
     * two tables it describes.
     *
     * @return array<string, array{weight_kg: float, count: int}>
     */
    private function lifecycleStats(int $userId): array
    {
        $collectionWeightKg = (float) FarmCollection::query()
            ->where('user_id', $userId)
            ->where('unit', 'kg')
            ->sum('quantity');
        $collectionCount = FarmCollection::query()->where('user_id', $userId)->count();

        $batchWeightKg = (float) Batch::query()->where('user_id', $userId)->sum('weight');
        $batchCount = Batch::query()->where('user_id', $userId)->count();

        $lotWeightKg = (float) Lot::query()->where('user_id', $userId)->sum('net_weight_kg');
        $lotCount = Lot::query()->where('user_id', $userId)->count();

        $tokenisedQuery = Lot::query()
            ->join('blockchains', 'blockchains.lot_id', '=', 'lots.id')
            ->where('lots.user_id', $userId);
        $tokenisedWeightKg = (float) (clone $tokenisedQuery)->sum('lots.net_weight_kg');
        $tokenisedCount = (clone $tokenisedQuery)->count();

        return [
            'collection' => ['weight_kg' => $collectionWeightKg, 'count' => $collectionCount],
            'batch' => ['weight_kg' => $batchWeightKg, 'count' => $batchCount],
            'lot' => ['weight_kg' => $lotWeightKg, 'count' => $lotCount],
            'tokenised' => ['weight_kg' => $tokenisedWeightKg, 'count' => $tokenisedCount],
        ];
    }

    /**
     * Every option list the "New Collection" / "New Batch" / "New Lot"
     * modals need — same source data as StoreController::inventoryContext(),
     * since these are the same modal components reused here rather than
     * rebuilt, just triggered from this page's own header buttons instead
     * of the store's "Register New ▾" dropdown.
     *
     * @return array<string, mixed>
     */
    private function modalOptions(): array
    {
        return [
            'processOptions' => ProcessingMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'dryingMethodOptions' => DryingMethodMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'millingOptions' => MillingMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
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
            'coffeeGradeOptions' => $this->coffeeGrades->activeOptions()->pluck('name')->all(),
            'packagingTypeOptions' => ['GrainPro', 'Jute Only', 'Vacuum'],
            'originOptions' => $this->countries->coffeeProducers()->pluck('name')->all(),
            'currencyOptions' => Currency::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->pluck('code'),
            'currencyCountries' => Currency::query()->pluck('country', 'code'),
            'flavorOptions' => FlavorMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'slug', 'name'])
                ->map(fn (FlavorMetadata $flavor): array => [
                    'id' => $flavor->id,
                    'slug' => $flavor->slug,
                    'name' => $flavor->name,
                ]),
            'bodyOptions' => BodyMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'slug', 'name'])
                ->map(fn (BodyMetadata $body): array => [
                    'id' => $body->id,
                    'slug' => $body->slug,
                    'name' => $body->name,
                ]),
            'acidityOptions' => AcidityMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'slug', 'name'])
                ->map(fn (AcidityMetadata $acidity): array => [
                    'id' => $acidity->id,
                    'slug' => $acidity->slug,
                    'name' => $acidity->name,
                ]),
            'aftertasteOptions' => AftertasteMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'slug', 'name'])
                ->map(fn (AftertasteMetadata $aftertaste): array => [
                    'id' => $aftertaste->id,
                    'slug' => $aftertaste->slug,
                    'name' => $aftertaste->name,
                ]),
            'aromaOptions' => AromaMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'slug', 'name'])
                ->map(fn (AromaMetadata $aroma): array => [
                    'id' => $aroma->id,
                    'slug' => $aroma->slug,
                    'name' => $aroma->name,
                ]),
        ];
    }
}
