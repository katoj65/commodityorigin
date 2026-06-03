<?php

namespace App\Http\Controllers\Lot;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\Lot;
use App\Models\Market;
use App\Models\MarketMetadata;
use App\Models\ProcessingMetadata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\LotRequest;

class LotController extends Controller
{
    /**
     * Show the legacy lot creation form.
     */
    public function create(): Response
    {
        Gate::authorize('create', Lot::class);

        $processOptions = $this->processingMethodOptions()
            ->pluck('name')
            ->all();

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
            'processOptions' => $processOptions,
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
            'lot_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'process' => [
                'required',
                'string',
                'max:100',
                Rule::exists('processing_metadata', 'name')->where('is_active', true),
            ],
            'grade' => ['required', 'string', 'max:100'],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'bag_weight_kg' => ['required', 'numeric', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'quality_score' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $imagePath = ImageUploadHelper::store($request->file('image'), 'lots');

        $lot = Lot::query()->create([
            ...$validated,
            'image' => $imagePath,
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
        $processingMethods = $this->processingMethodOptions()
            ->map(fn (ProcessingMetadata $method): array => [
                'id' => $method->id,
                'slug' => $method->slug,
                'name' => $method->name,
                'description' => $method->description,
            ])
            ->all();
        $targetMarkets = $this->targetMarketOptions()
            ->map(fn (MarketMetadata $market): array => [
                'id' => $market->id,
                'slug' => $market->slug,
                'name' => $market->name,
                'description' => $market->description,
            ])
            ->all();

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
                'lot_number' => '',
                'lot_name' => '',
                'description' => '',
                'image' => '',
                'allocation_kg' => '',
                'net_weight_kg' => '',
                'quantity_bags' => '',
                'bag_weight_kg' => '',
                'grade' => '',
                'process' => '',
                'warehouse' => '',
                'packaging_type' => '',
                'screen_size' => '',
                'aroma_score' => '',
                'acidity_score' => '',
                'body_score' => '',
                'target_market' => '',
                'price_per_kg' => '',
                'tokenize' => false,
            ],
            'options' => [
                'packaging_types' => ['GrainPro', 'Jute Only', 'Vacuum'],
                'processing_methods' => $processingMethods,
                'target_markets' => $targetMarkets,
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
            'lot_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'allocation_kg' => ['required', 'numeric', 'gt:0'],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'bag_weight_kg' => ['required', 'numeric', 'min:1'],
            'grade' => ['required', 'string', 'max:100'],
            'process' => [
                'required',
                'string',
                'max:100',
                Rule::exists('processing_metadata', 'name')->where('is_active', true),
            ],
            'warehouse' => ['nullable', 'string', 'max:255'],
            'packaging_type' => ['required', 'string', Rule::in(['GrainPro', 'Jute Only', 'Vacuum'])],
            'screen_size' => ['nullable', 'string', 'max:100'],
            'aroma_score' => ['required', 'numeric', 'between:0,10'],
            'acidity_score' => ['required', 'numeric', 'between:0,10'],
            'body_score' => ['required', 'numeric', 'between:0,10'],
            'target_market' => [
                'required',
                'string',
                'max:255',
                Rule::when(
                    fn ($input) => $input->target_market !== 'All',
                    Rule::exists('market_metadata', 'name')->where('is_active', true),
                ),
            ],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'tokenize' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'submission_intent' => ['nullable', 'string', Rule::in(['draft', 'create', 'create_and_tokenise', 'create_and_list'])],
        ]);

        $imagePath = ImageUploadHelper::store($request->file('image'), 'lots');

        if ($remainingQtyKg <= 0) {
            throw ValidationException::withMessages([
                'allocation_kg' => 'This batch is fully allocated. No remaining quantity is available for a new lot.',
            ]);
        }

        if ((float) $validated['allocation_kg'] > (float) $batch->weight) {
            throw ValidationException::withMessages([
                'allocation_kg' => sprintf(
                    'Lot allocation cannot exceed the total batch weight of %.2f kg.',
                    (float) $batch->weight
                ),
            ]);
        }

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


        $lot = Lot::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $request->user()->id,
            'lot_number' => $validated['lot_number'],
            'lot_name' => $validated['lot_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'process' => $validated['process'],
            'grade' => $validated['grade'],
            'quantity_bags' => $validated['quantity_bags'],
            'bag_weight_kg' => $validated['bag_weight_kg'],
            'packaging_type' => $validated['packaging_type'],
            'net_weight_kg' => round((float) $validated['allocation_kg'], 2),
            'price' => $validated['price_per_kg'],
            'quality_score' => $batch->cup_score ?: $sensoryAverage,
            'status' => $this->resolveLotStatus($validated['submission_intent'] ?? 'create'),
            'notes' => $validated['notes'] ?? null,
        ]);

        $message = $remainingQtyKg - (float) $validated['allocation_kg'] <= 0
            ? 'Lot created and the batch is now fully allocated.'
            : 'Lot created successfully from the selected batch.';

        return redirect()
            ->route('lot.show', $lot)
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
     * Fetch active processing methods from metadata.
     *
     * @return Collection<int, ProcessingMetadata>
     */
    private function processingMethodOptions(): Collection
    {
        return ProcessingMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Fetch active coffee target market options from metadata.
     *
     * @return Collection<int, MarketMetadata>
     */
    private function targetMarketOptions(): Collection
    {
        return MarketMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
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


public function show(Lot $lot): Response
{
    $lot->loadMissing('batch');

    return Inertia::render('Lot/LotProfile', [
        'lot' => $lot,
    ]);
}

public function publish(Lot $lot): RedirectResponse
{
    Gate::authorize('update', $lot);

    if (Market::where('lot_id', $lot->id)->exists()) {
        return back()->with('error', 'This lot is already published to the market.');
    }

    $lot->loadMissing('batch');

    Market::create([
        'lot_id'        => $lot->id,
        'user_id'       => auth()->id(),
        'lot_code'      => $lot->lot_number,
        'name'          => $lot->lot_name ?? $lot->lot_number,
        'origin'        => $lot->batch?->warehouse_location,
        'type'          => $lot->batch?->variety ?? 'Arabica',
        'process'       => $lot->process,
        'quality_score' => $lot->quality_score,
        'quantity'      => $lot->net_weight_kg,
        'price_per_kg'  => $lot->price,
        'target_market' => $lot->target_market,
        'status'        => 'live',
        'image'         => $lot->image,
    ]);

    return back()->with('success', 'Lot published to market successfully.');
}




//publish


public function publishLot(){



}

public function lotTraceability(Lot $lot): Response
{
    $lot->loadMissing(['batch.season']);

    $batch  = $lot->batch;
    $season = $batch?->season;

    return Inertia::render('Lot/LotTraceability', [
        'lot' => [
            'id'             => $lot->id,
            'lot_number'     => $lot->lot_number,
            'lot_name'       => $lot->lot_name,
            'status'         => $lot->status,
            'process'        => $lot->process,
            'grade'          => $lot->grade,
            'net_weight_kg'  => (float) ($lot->net_weight_kg ?? 0),
            'quality_score'  => (float) ($lot->quality_score ?? 0),
            'price_per_kg'   => (float) ($lot->price ?? 0),
            'packaging_type' => $lot->packaging_type,
            'created_at'     => $lot->created_at?->format('d M Y'),
            'image'          => $lot->image,
        ],
        'batch' => $batch ? [
            'id'                 => $batch->id,
            'batch_number'       => $batch->batch_number,
            'variety'            => $batch->variety,
            'processing_method'  => $batch->processing_method,
            'moisture_content'   => (float) ($batch->moisture_content ?? 0),
            'cup_score'          => (float) ($batch->cup_score ?? 0),
            'weight'             => (float) ($batch->weight ?? 0),
            'warehouse_location' => $batch->warehouse_location,
        ] : null,
        'season' => $season ? [
            'id'         => $season->id,
            'name'       => $season->name,
            'start_date' => $season->start_date?->format('d M Y'),
            'end_date'   => $season->end_date?->format('d M Y'),
            'status'     => $season->status,
        ] : null,
    ]);
}


public function index(): Response
{
    return Inertia::render('Lot/LotPage', [
        'lots'    => ['data' => [], 'meta' => ['total' => 0, 'current_page' => 1, 'last_page' => 1, 'from' => 0, 'to' => 0]],
        'filters' => [],
    ]);
}



public function storeLotRequest(Request $request)
//: RedirectResponse
{
    $validated = $request->validate([
        'crop_type' => ['required', 'string', 'max:255'],
        'variety' => ['nullable', 'string', 'max:255'],
        'grade' => ['required', 'string', 'max:255'],
        'amount' => ['nullable'],
        'quantity' => ['required', 'numeric', 'min:0.01'],
        'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    $userId = $request->user()->id;
    LotRequest::create(array_merge($validated, ['user_id' => $userId]));

   return redirect()->back()->with('success', 'Your lot request has been submitted successfully.');

}




public function showLotRequest(Request $request, LotRequest $lotRequest): Response
{
    $lotRequest->load('user');

    return Inertia::render('Lot/LotRequestDetails', [
        'lotRequest' => $lotRequest,
        'canEdit'    => $request->user()->can('update', $lotRequest),
        'canDelete'  => $request->user()->can('delete', $lotRequest),
    ]);
}

public function updateLotRequest(Request $request, LotRequest $lotRequest): RedirectResponse
{
    Gate::authorize('update', $lotRequest);

    $data = $request->validate([
        'crop_type' => ['required', 'string', 'max:100'],
        'variety'   => ['nullable', 'string', 'max:100'],
        'grade'     => ['nullable', 'string', 'max:50'],
        'quantity'  => ['required', 'numeric', 'min:0.01'],
        'amount'    => ['nullable', 'numeric', 'min:0'],
        'notes'     => ['nullable', 'string', 'max:2000'],
    ]);

    $lotRequest->update($data);

    return redirect()->route('lot.request.show', $lotRequest)
        ->with('success', 'Lot request updated.');
}

public function destroyLotRequest(LotRequest $lotRequest): RedirectResponse
{
    Gate::authorize('delete', $lotRequest);

    $lotRequest->delete();

    return redirect()->route('dashboard')
        ->with('success', 'Lot request deleted.');
}













}
