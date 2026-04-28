<?php

namespace App\Http\Controllers\Lot;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\Lot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LotController extends Controller
{
    /**
     * Show the legacy lot creation form.
     */
    public function create(): Response
    {
        Gate::authorize('create', Lot::class);

        $batches = Batch::query()
            ->orderBy('batch_number')
            ->get()
            ->map(fn (Batch $batch): array => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'variety' => $batch->variety,
                'warehouse_location' => $batch->warehouse_location,
                'quantity_bags' => $batch->quantity,
                'net_weight_kg' => (float) $batch->weight,
                'quality_score' => $batch->cup_score ? (float) $batch->cup_score : null,
                'processing_method' => $batch->processing_method,
                'status' => $batch->status,
            ])
            ->values();

        return Inertia::render('Lot/Create', [
            'batches' => $batches,
            'processOptions' => ['Washed', 'Natural', 'Honey', 'Anaerobic'],
        ]);
    }

    /**
     * Store a newly created legacy lot.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Lot::class);

        $validated = $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'lot_number' => ['required', 'string', 'max:100', 'unique:lots,lot_number'],
            'process' => ['required', 'string', 'max:100'],
            'grade' => ['required', 'string', 'max:100'],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'bag_weight_kg' => ['required', 'numeric', 'min:1'],
            'reserve_price' => ['nullable', 'numeric', 'min:0'],
            'quality_score' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lot = Lot::query()->create([
            ...$validated,
            'net_weight_kg' => round((float) $validated['quantity_bags'] * (float) $validated['bag_weight_kg'], 2),
            'user_id' => $request->user()->id,
            'status' => 'draft',
        ]);

        return redirect()
            ->route('batch.show', $lot->batch_id)
            ->with('success', 'Lot added successfully.');
    }

    /**
     * Show the batch-scoped lot creation page.
     */
    public function createLot(Batch $batch): Response
    {
        Gate::authorize('create', [Lot::class, $batch]);

        [$sourceFarm, $allocatedQtyKg, $remainingQtyKg] = $this->resolveBatchMetrics($batch);
        $qualityScore = (float) ($batch->cup_score ?: 87.2);
        $defaultAllocationKg = $remainingQtyKg > 0 ? min($remainingQtyKg, 400.0) : 0.0;
        $defaultPricePerKg = $batch->weight && $batch->price
            ? round((float) $batch->price / max((float) $batch->weight, 1), 2)
            : 12.50;

        return Inertia::render('Lot/CreateLotPage', [
            'batch' => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'variety' => $batch->variety,
                'warehouse_location' => $batch->warehouse_location,
                'processing_method' => $batch->processing_method,
                'screen_size' => $batch->screen_size,
                'cup_score' => $qualityScore,
                'net_weight_kg' => (float) $batch->weight,
                'price' => (float) ($batch->price ?? 0),
            ],
            'sourceBatch' => [
                'label' => '#'.($batch->batch_number ?: 'BTC-'.str_pad((string) $batch->id, 2, '0', STR_PAD_LEFT)),
                'season' => $batch->season?->name ?: (optional($batch->processing_date)?->format('Y / m') ?: 'Pending'),
                'origin' => $sourceFarm?->location
                    ?: $sourceFarm?->farmer?->district
                    ?: 'Origin pending',
                'type' => $batch->variety ?: 'Heirloom Arabica',
                'available_qty_kg' => $remainingQtyKg,
                'quality_score' => $qualityScore,
                'batch_total_kg' => (float) $batch->weight,
                'allocated_qty_kg' => $allocatedQtyKg,
                'remaining_qty_kg' => $remainingQtyKg,
                'warehouse' => $batch->warehouse_location,
                'altitude' => $sourceFarm?->altitude,
                'source_farm_name' => $sourceFarm?->name,
            ],
            'defaults' => [
                'lot_number' => $this->makeLotNumber($batch),
                'lot_name' => trim(($batch->variety ?: 'Sidama Bensa').' - Special Selection'),
                'allocation_kg' => $defaultAllocationKg,
                'net_weight_kg' => $defaultAllocationKg,
                'quantity_bags' => max(1, (int) ceil(max($defaultAllocationKg, 60) / 60)),
                'bag_weight_kg' => 60,
                'grade' => $this->inferGrade((float) ($batch->cup_score ?? 0)),
                'warehouse' => $batch->warehouse_location ?: 'Warehouse release pending',
                'packaging_type' => 'GrainPro',
                'screen_size' => $batch->screen_size ?: '17/18',
                'altitude' => $sourceFarm?->altitude ?: '1,950 - 2,100',
                'aroma_score' => 8.75,
                'acidity_score' => 9.00,
                'body_score' => 8.25,
                'target_market' => 'United Arab Emirates (Specialty)',
                'price_per_kg' => $defaultPricePerKg,
                'tokenize' => true,
            ],
            'options' => [
                'packaging_types' => ['GrainPro', 'Jute Only', 'Vacuum'],
                'target_markets' => [
                    'United Arab Emirates (Specialty)',
                    'Scandinavia Specialty Buyers',
                    'Japan Premium Roasters',
                    'Germany Green Coffee Importers',
                ],
            ],
            'canSubmit' => (bool) $sourceFarm,
            'submissionBlockedMessage' => $sourceFarm
                ? null
                : 'Link a harvest with a source farm to this batch before creating a lot.',
        ]);
    }

    /**
     * Store a newly created lot against a batch.
     */
    public function storeFromBatch(Request $request, Batch $batch): RedirectResponse
    {
        Gate::authorize('create', [Lot::class, $batch]);

        [$sourceFarm, $allocatedQtyKg, $remainingQtyKg] = $this->resolveBatchMetrics($batch);

        if (! $sourceFarm) {
            throw ValidationException::withMessages([
                'batch' => 'This batch must be linked to at least one harvest farm before a lot can be created.',
            ]);
        }

        $validated = $request->validate([
            'lot_number' => ['required', 'string', 'max:100', 'unique:lots,lot_number'],
            'lot_name' => ['required', 'string', 'max:255'],
            'allocation_kg' => ['required', 'numeric', 'gt:0'],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'bag_weight_kg' => ['required', 'numeric', 'min:1'],
            'grade' => ['required', 'string', 'max:100'],
            'warehouse' => ['required', 'string', 'max:255'],
            'packaging_type' => ['required', 'string', Rule::in(['GrainPro', 'Jute Only', 'Vacuum'])],
            'screen_size' => ['nullable', 'string', 'max:100'],
            'altitude' => ['nullable', 'string', 'max:100'],
            'aroma_score' => ['required', 'numeric', 'between:0,10'],
            'acidity_score' => ['required', 'numeric', 'between:0,10'],
            'body_score' => ['required', 'numeric', 'between:0,10'],
            'target_market' => ['required', 'string', 'max:255'],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'tokenize' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'submission_intent' => ['nullable', 'string', Rule::in(['draft', 'create', 'create_and_tokenise', 'create_and_list'])],
        ]);

        if ((float) $validated['allocation_kg'] > $remainingQtyKg) {
            throw ValidationException::withMessages([
                'allocation_kg' => sprintf(
                    'Lot allocation cannot exceed the remaining batch quantity of %.2f kg.',
                    $remainingQtyKg
                ),
            ]);
        }

        $sensoryAverage = round(collect([
            $validated['aroma_score'],
            $validated['acidity_score'],
            $validated['body_score'],
        ])->avg(), 2);

        Lot::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $request->user()->id,
            'lot_number' => $validated['lot_number'],
            'lot_name' => $validated['lot_name'],
            'process' => $batch->processing_method ?: 'Washed',
            'grade' => $validated['grade'],
            'allocation_kg' => $validated['allocation_kg'],
            'net_weight_kg' => round((float) $validated['allocation_kg'], 2),
            'quantity_bags' => $validated['quantity_bags'],
            'bag_weight_kg' => $validated['bag_weight_kg'],
            'reserve_price' => $validated['price_per_kg'],
            'quality_score' => $batch->cup_score ?: $sensoryAverage,
            'warehouse' => $validated['warehouse'],
            'packaging_type' => $validated['packaging_type'],
            'screen_size' => $validated['screen_size'] ?: $batch->screen_size,
            'altitude' => $validated['altitude'],
            'aroma_score' => $validated['aroma_score'],
            'acidity_score' => $validated['acidity_score'],
            'body_score' => $validated['body_score'],
            'target_market' => $validated['target_market'],
            'price_per_kg' => $validated['price_per_kg'],
            'tokenize' => (bool) ($validated['tokenize'] ?? false),
            'status' => $this->resolveLotStatus($validated['submission_intent'] ?? 'create'),
            'notes' => $validated['notes'] ?? null,
        ]);

        $message = $remainingQtyKg - (float) $validated['allocation_kg'] <= 0
            ? 'Lot created and the batch is now fully allocated.'
            : 'Lot created successfully from the selected batch.';

        return redirect()
            ->route('batch.show', $batch)
            ->with('success', $message);
    }

    /**
     * Resolve the source farm plus current allocation numbers for a batch.
     *
     * @return array{0: ?Farm, 1: float, 2: float}
     */
    private function resolveBatchMetrics(Batch $batch): array
    {
        $batch->loadMissing(['season', 'ownerships', 'lots']);

        $harvests = $this->resolveBatchHarvests($batch);
        $sourceFarm = $harvests->first()?->farm;
        $allocatedQtyKg = $this->allocatedQuantityKg($batch);
        $remainingQtyKg = max(round((float) $batch->weight - $allocatedQtyKg, 2), 0.0);

        return [$sourceFarm, $allocatedQtyKg, $remainingQtyKg];
    }

    /**
     * Resolve harvests linked to the batch.
     *
     * @return Collection<int, Harvest>
     */
    private function resolveBatchHarvests(Batch $batch): Collection
    {
        $harvestIds = $batch->ownerships
            ->where('owner_type', Harvest::class)
            ->pluck('owner_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->values();

        $query = Harvest::query()
            ->with(['farm.farmer'])
            ->orderBy('harvest_date');

        if ($harvestIds->isNotEmpty()) {
            return $query->whereKey($harvestIds)->get();
        }

        if ($batch->season_id) {
            return $query->where('season_id', $batch->season_id)->get();
        }

        return collect();
    }

    /**
     * Calculate the quantity already allocated to lots from this batch.
     */
    private function allocatedQuantityKg(Batch $batch): float
    {
        $lots = $batch->relationLoaded('lots')
            ? $batch->lots
            : $batch->lots()->get();

        return round($lots->sum(function (Lot $lot): float {
            if ($lot->allocation_kg !== null) {
                return (float) $lot->allocation_kg;
            }

            return (float) $lot->quantity_bags * (float) $lot->bag_weight_kg;
        }), 2);
    }

    /**
     * Build the next lot number suggestion for the batch.
     */
    private function makeLotNumber(Batch $batch): string
    {
        $base = Str::of($batch->batch_number ?: 'BATCH-'.$batch->id)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '-')
            ->trim('-')
            ->value();

        $count = $batch->relationLoaded('lots')
            ? $batch->lots->count()
            : $batch->lots()->count();

        return sprintf('LOT-%s-%02d', $base, $count + 1);
    }

    /**
     * Infer a grade label from the batch cup score.
     */
    private function inferGrade(float $cupScore): string
    {
        return match (true) {
            $cupScore >= 86 => 'A1',
            $cupScore >= 84 => 'A2',
            $cupScore >= 82 => 'B',
            default => 'Commercial',
        };
    }

    /**
     * Resolve the lot status from the submission intent.
     */
    private function resolveLotStatus(string $intent): string
    {
        return match ($intent) {
            'draft' => 'draft',
            'create_and_tokenise' => 'tokenisation_ready',
            'create_and_list' => 'listing_ready',
            default => 'ready',
        };
    }

}
