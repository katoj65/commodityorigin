<?php

namespace App\Http\Controllers\Lot;

use App\Helpers\ImageUploadHelper;
use App\Helpers\QrCodeHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\LotActivityResource;
use App\Http\Resources\LotResource;
use App\Models\AcidityMetadata;
use App\Models\AftertasteMetadata;
use App\Models\AromaMetadata;
use App\Models\Batch;
use App\Models\BodyMetadata;
use App\Models\Currency;
use App\Models\DeliveryMethodMetadata;
use App\Models\FlavorMetadata;
use App\Models\IncotermMetadata;
use App\Models\Lot;
use App\Models\LotActivity;
use App\Models\LotActivityMetadata;
use App\Models\LotImage;
use App\Models\LotRequest;
use App\Services\CoffeeGradeService;
use App\Services\CountryService;
use App\Services\LotActivityService;
use App\Services\LotImageService;
use App\Services\LotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LotController extends Controller
{
    public function __construct(
        private readonly LotService $lots,
        private readonly CoffeeGradeService $coffeeGrades,
        private readonly CountryService $countries,
        private readonly LotImageService $lotImages,
        private readonly LotActivityService $activities,
    ) {
    }

    /**
     * Show the legacy lot creation form.
     */
    public function create(): Response
    {
        Gate::authorize('create', Lot::class);

        return Inertia::render('Lot/Create', [
            'processOptions' => $this->lots->processingMethodOptions()->pluck('name')->all(),
            'coffeeGradeOptions' => $this->coffeeGrades->activeOptions()->pluck('name')->all(),
        ]);
    }

    /**
     * Store a newly created lot. The lot number is generated
     * automatically; linking a batch happens afterward from the lot
     * profile page.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Lot::class);

        $validated = $request->validate([
            'lot_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'image' => ImageUploadHelper::rules(),
            'process' => [
                'required',
                'string',
                'max:100',
                Rule::exists('processing_metadata', 'name')->where('is_active', true),
            ],
            'grade' => [
                'required',
                'string',
                'max:100',
                Rule::exists('coffee_grades', 'name')->where('is_active', true),
            ],
            'variety' => [
                'required',
                'string',
                'max:100',
                Rule::exists('crop_variety_metadata', 'name')->where('is_active', true),
            ],
            'origin' => [
                'required',
                'string',
                'max:100',
                Rule::exists('countries', 'name')->where('is_coffee_producer', true),
            ],
            'region' => ['required', 'string', 'max:100'],
            'altitude' => ['nullable', 'numeric', 'between:0,5000'],
            'year_of_harvest' => ['required', 'integer', 'digits:4'],
            'moisture' => ['required', 'numeric', 'between:0,100'],
            'defects_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'screen' => ['required', 'string', 'max:50'],
            'packaging_type' => ['nullable', 'string', Rule::in(['GrainPro', 'Jute Only', 'Vacuum'])],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'bag_weight_kg' => ['required', 'numeric', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'quality_score' => ['nullable', 'numeric', 'between:0,100'],
            'acidity' => ['nullable', 'string', Rule::exists('acidity_metadata', 'slug')->where('is_active', true)],
            'body' => ['nullable', 'string', Rule::exists('body_metadata', 'slug')->where('is_active', true)],
            'flavor' => ['nullable', 'string', Rule::exists('flavor_metadata', 'slug')->where('is_active', true)],
            'aroma' => ['nullable', 'string', Rule::exists('aroma_metadata', 'slug')->where('is_active', true)],
            'balance' => ['nullable', 'numeric', 'between:0,10'],
            'aftertaste' => ['nullable', 'string', Rule::exists('aftertaste_metadata', 'slug')->where('is_active', true)],
            'flavor_ids' => ['nullable', 'array'],
            'flavor_ids.*' => ['integer', 'exists:flavor_metadata,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lot = $this->lots->create($validated, $request->file('image'), $request->user()->id);

        return redirect()
            ->route('lot.show', $lot)
            ->with('success', 'Lot added successfully.');
    }

    /**
     * Show the batch-scoped lot creation page.
     */
    public function createLot(Batch $batch): Response
    {
        Gate::authorize('create', [Lot::class, $batch]);

        $metrics = $this->lots->batchMetrics($batch);
        $sourceFarm = $metrics['source_farm'];
        $qualityScore = (float) ($batch->cup_score ?: 87.2);

        $processingMethods = $this->lots->processingMethodOptions()
            ->map(fn ($method): array => [
                'id' => $method->id,
                'slug' => $method->slug,
                'name' => $method->name,
                'description' => $method->description,
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
                'origin' => $sourceFarm?->district
                    ?: 'Origin pending',
                'type' => $batch->variety ?: 'Heirloom Arabica',
                'available_qty_kg' => $metrics['remaining_kg'],
                'quality_score' => $qualityScore,
                'batch_total_kg' => (float) $batch->weight,
                'allocated_qty_kg' => $metrics['allocated_kg'],
                'remaining_qty_kg' => $metrics['remaining_kg'],
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
                'packaging_type' => '',
                'quality_score' => $qualityScore,
                'acidity' => '',
                'body' => '',
                'flavor' => '',
                'aroma' => '',
                'balance' => '',
                'aftertaste' => '',
                'flavor_ids' => [],
                'price' => '',
            ],
            'options' => [
                'packaging_types' => ['GrainPro', 'Jute Only', 'Vacuum'],
                'processing_methods' => $processingMethods,
                'flavors' => $this->lots->activeFlavorOptions()->map(fn (FlavorMetadata $flavor): array => [
                    'id' => $flavor->id,
                    'slug' => $flavor->slug,
                    'name' => $flavor->name,
                ]),
                'bodies' => $this->lots->activeBodyOptions()->map(fn (BodyMetadata $body): array => [
                    'id' => $body->id,
                    'slug' => $body->slug,
                    'name' => $body->name,
                ]),
                'acidities' => $this->lots->activeAcidityOptions()->map(fn (AcidityMetadata $acidity): array => [
                    'id' => $acidity->id,
                    'slug' => $acidity->slug,
                    'name' => $acidity->name,
                ]),
                'aftertastes' => $this->lots->activeAftertasteOptions()->map(fn (AftertasteMetadata $aftertaste): array => [
                    'id' => $aftertaste->id,
                    'slug' => $aftertaste->slug,
                    'name' => $aftertaste->name,
                ]),
                'aromas' => $this->lots->activeAromaOptions()->map(fn (AromaMetadata $aroma): array => [
                    'id' => $aroma->id,
                    'slug' => $aroma->slug,
                    'name' => $aroma->name,
                ]),
            ],
            'canSubmit' => (bool) $sourceFarm,
            'submissionBlockedMessage' => $sourceFarm
                ? null
                : 'Link a farm collection with a source farm to this batch before creating a lot.',
        ]);
    }

    /**
     * Store a newly created lot against a batch.
     */
    public function storeFromBatch(Request $request, Batch $batch): RedirectResponse
    {
        Gate::authorize('create', [Lot::class, $batch]);

        $validated = $request->validate([
            'lot_number' => ['required', 'string', 'max:100', 'unique:lots,lot_number'],
            'lot_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'image' => ImageUploadHelper::rules(),
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
            'packaging_type' => ['required', 'string', Rule::in(['GrainPro', 'Jute Only', 'Vacuum'])],
            'quality_score' => ['nullable', 'numeric', 'between:0,100'],
            'acidity' => ['nullable', 'string', Rule::exists('acidity_metadata', 'slug')->where('is_active', true)],
            'body' => ['nullable', 'string', Rule::exists('body_metadata', 'slug')->where('is_active', true)],
            'flavor' => ['nullable', 'string', Rule::exists('flavor_metadata', 'slug')->where('is_active', true)],
            'aroma' => ['nullable', 'string', Rule::exists('aroma_metadata', 'slug')->where('is_active', true)],
            'balance' => ['nullable', 'numeric', 'between:0,10'],
            'aftertaste' => ['nullable', 'string', Rule::exists('aftertaste_metadata', 'slug')->where('is_active', true)],
            'flavor_ids' => ['nullable', 'array'],
            'flavor_ids.*' => ['integer', 'exists:flavor_metadata,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'submission_intent' => ['nullable', 'string', Rule::in(['draft', 'create', 'create_and_tokenise', 'create_and_list'])],
        ]);

        $result = $this->lots->createFromBatch($validated, $request->file('image'), $batch, $request->user()->id);

        $message = $result['fully_allocated']
            ? 'Lot created and the batch is now fully allocated.'
            : 'Lot created successfully from the selected batch.';

        return redirect()
            ->route('lot.show', $result['lot'])
            ->with('success', $message);
    }

    /**
     * Show the lot profile page.
     */
    public function show(Lot $lot): Response
    {
        $this->lots->ensureQrCode($lot);

        $lot->load([
            'lotBatches.batch.user',
            'lotBatches.batch.batchFarmCollections.farmCollection.farm',
            'user',
            'market',
            'images',
            'flavors',
        ]);

        return Inertia::render('Lot/LotProfile', [
            'lot' => LotResource::make($lot)->resolve(),
            'processOptions' => $this->lots->processingMethodOptions()->pluck('name')->all(),
            'coffeeGradeOptions' => $this->coffeeGrades->activeOptions()->pluck('name')->all(),
            'varietyOptions' => $this->lots->varietyOptions()->pluck('name')->all(),
            'originOptions' => $this->countries->coffeeProducers()->pluck('name')->all(),
            'packagingTypeOptions' => ['GrainPro', 'Jute Only', 'Vacuum'],
            'flavorOptions' => $this->lots->activeFlavorOptions()->map(fn (FlavorMetadata $flavor): array => [
                'id' => $flavor->id,
                'slug' => $flavor->slug,
                'name' => $flavor->name,
            ]),
            'bodyOptions' => $this->lots->activeBodyOptions()->map(fn (BodyMetadata $body): array => [
                'id' => $body->id,
                'slug' => $body->slug,
                'name' => $body->name,
            ]),
            'acidityOptions' => $this->lots->activeAcidityOptions()->map(fn (AcidityMetadata $acidity): array => [
                'id' => $acidity->id,
                'slug' => $acidity->slug,
                'name' => $acidity->name,
            ]),
            'aftertasteOptions' => $this->lots->activeAftertasteOptions()->map(fn (AftertasteMetadata $aftertaste): array => [
                'id' => $aftertaste->id,
                'slug' => $aftertaste->slug,
                'name' => $aftertaste->name,
            ]),
            'aromaOptions' => $this->lots->activeAromaOptions()->map(fn (AromaMetadata $aroma): array => [
                'id' => $aroma->id,
                'slug' => $aroma->slug,
                'name' => $aroma->name,
            ]),
            'deliveryMethodOptions' => DeliveryMethodMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'name'])
                ->map(fn (DeliveryMethodMetadata $option): array => [
                    'slug' => $option->slug,
                    'name' => $option->name,
                ]),
            'incotermOptions' => IncotermMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'name'])
                ->map(fn (IncotermMetadata $option): array => [
                    'slug' => $option->slug,
                    'name' => $option->name,
                ]),
            'currencyOptions' => Currency::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->pluck('code'),
            'currencyCountries' => Currency::query()->pluck('country', 'code'),
            'activities' => LotActivityResource::collection($this->activities->forLot($lot))->resolve(),
            'activityOptions' => LotActivityMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'name'])
                ->map(fn (LotActivityMetadata $option): array => [
                    'slug' => $option->slug,
                    'name' => $option->name,
                ]),
        ]);
    }

    /**
     * Update the specified lot.
     */
    public function update(Request $request, Lot $lot): RedirectResponse
    {
        Gate::authorize('update', $lot);

        $validated = $request->validate([
            'lot_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'image' => ImageUploadHelper::rules(),
            'process' => [
                'required',
                'string',
                'max:100',
                Rule::exists('processing_metadata', 'name')->where('is_active', true),
            ],
            'grade' => [
                'required',
                'string',
                'max:100',
                Rule::exists('coffee_grades', 'name')->where('is_active', true),
            ],
            'variety' => [
                'required',
                'string',
                'max:100',
                Rule::exists('crop_variety_metadata', 'name')->where('is_active', true),
            ],
            'origin' => [
                'required',
                'string',
                'max:100',
                Rule::exists('countries', 'name')->where('is_coffee_producer', true),
            ],
            'region' => ['required', 'string', 'max:100'],
            'altitude' => ['nullable', 'numeric', 'between:0,5000'],
            'year_of_harvest' => ['required', 'integer', 'digits:4'],
            'moisture' => ['required', 'numeric', 'between:0,100'],
            'defects_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'screen' => ['required', 'string', 'max:50'],
            'packaging_type' => ['nullable', 'string', Rule::in(['GrainPro', 'Jute Only', 'Vacuum'])],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'bag_weight_kg' => ['required', 'numeric', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'quality_score' => ['nullable', 'numeric', 'between:0,100'],
            'acidity' => ['nullable', 'string', Rule::exists('acidity_metadata', 'slug')->where('is_active', true)],
            'body' => ['nullable', 'string', Rule::exists('body_metadata', 'slug')->where('is_active', true)],
            'flavor' => ['nullable', 'string', Rule::exists('flavor_metadata', 'slug')->where('is_active', true)],
            'aroma' => ['nullable', 'string', Rule::exists('aroma_metadata', 'slug')->where('is_active', true)],
            'balance' => ['nullable', 'numeric', 'between:0,10'],
            'aftertaste' => ['nullable', 'string', Rule::exists('aftertaste_metadata', 'slug')->where('is_active', true)],
            'flavor_ids' => ['nullable', 'array'],
            'flavor_ids.*' => ['integer', 'exists:flavor_metadata,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->lots->update($lot, $validated, $request->file('image'));

        return back()->with('success', 'Lot updated successfully.');
    }

    /**
     * Delete the specified lot.
     */
    public function destroy(Lot $lot): RedirectResponse
    {
        Gate::authorize('delete', $lot);

        $this->lots->destroy($lot);

        return redirect()->route('store.show')->with('success', 'Lot deleted successfully.');
    }

    /**
     * Link a batch to this lot, found by its batch_number, via the
     * lot_batch pivot table.
     */
    public function attachBatch(Request $request, Lot $lot): RedirectResponse
    {
        Gate::authorize('update', $lot);

        $validated = $request->validate([
            'batch_number' => ['required', 'string', 'max:255'],
        ]);

        $this->lots->attachBatchByNumber($lot, $validated['batch_number'], $request->user()->id);

        return back()->with('success', 'Batch linked to this lot.');
    }

    /**
     * Record a manual activity-log entry for this lot — `event` must be
     * an active slug in lot_activity_metadata.
     */
    public function storeActivity(Request $request, Lot $lot): RedirectResponse
    {
        Gate::authorize('update', $lot);

        $validated = $request->validate([
            'event' => [
                'required',
                'string',
                Rule::exists('lot_activity_metadata', 'slug')->where('is_active', true),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->activities->record($lot, $validated['event'], $validated['description'] ?? null, $request->user()->id);

        return back()->with('success', 'Activity recorded.');
    }

    /**
     * Remove one activity-log entry from this lot.
     */
    public function destroyActivity(Lot $lot, LotActivity $activity): RedirectResponse
    {
        Gate::authorize('update', $lot);
        abort_unless((int) $activity->lot_id === (int) $lot->id, 404);

        $this->activities->delete($activity);

        return back()->with('success', 'Activity removed.');
    }

    /**
     * Publish a lot to the live market.
     */
    public function publish(Request $request, Lot $lot): RedirectResponse
    {
        Gate::authorize('update', $lot);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'available_quantity' => ['nullable', 'numeric', 'min:0', 'lte:quantity'],
            'unit' => ['nullable', 'string', 'max:20'],
            'currency' => ['nullable', 'string', 'size:3'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'pricing_type' => ['nullable', 'string', Rule::in(['fixed', 'negotiable', 'auction'])],
            'minimum_order_quantity' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'delivery_terms' => ['nullable', 'string', 'max:255'],
            'delivery_location' => ['nullable', 'string', 'max:255'],
            'available_from' => ['nullable', 'string', 'max:255'],
            'delivery_method' => ['nullable', 'string', Rule::exists('delivery_method_metadata', 'slug')->where('is_active', true)],
            'incoterm' => ['required', 'string', Rule::exists('incoterm_metadata', 'slug')->where('is_active', true)],
            'dispatch' => ['nullable', 'string', 'max:255'],
            'transport_arrangement' => ['nullable', 'string', 'max:255'],
            'insurance_arrangement' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        if (! $this->lots->publish($lot, $request->user(), $validated)) {
            return back()->with('error', 'This lot is already published to the market.');
        }

        return back()->with('success', 'Lot published to market successfully.');
    }

    /**
     * Add up to LotImageService::MAX_IMAGES gallery photos to a lot —
     * owner only. Extra files beyond the remaining slots are silently
     * dropped rather than erroring.
     */
    public function storeImages(Request $request, Lot $lot): RedirectResponse
    {
        Gate::authorize('update', $lot);

        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ImageUploadHelper::itemRules(),
        ]);

        $this->lotImages->store($lot, $validated['images']);

        return back();
    }

    /**
     * Remove one gallery photo from a lot — owner only.
     */
    public function destroyImage(Lot $lot, LotImage $image): RedirectResponse
    {
        Gate::authorize('update', $lot);
        abort_unless($image->lot_id === $lot->id, 404);

        $this->lotImages->delete($image);

        return back();
    }

    /**
     * Remove a lot's live market listing.
     */
    public function unpublish(Lot $lot): RedirectResponse
    {
        Gate::authorize('update', $lot);

        $this->lots->unpublish($lot);

        return back()->with('success', 'Lot removed from the market.');
    }

    /**
     * Placeholder for tokenized lot publishing — not yet implemented.
     */
    public function publishLot(): void {}

    /**
     * Show the lot traceability page. Eager-loads the full origin chain —
     * batches, their farm collections, each collection's farm, and that
     * farm's farmers — plus the users who recorded each step.
     */
    public function lotTraceability(Lot $lot): Response
    {
        $lot->load([
            'lotBatches.batch.batchFarmCollections.farmCollection.farm.farmers',
            'lotBatches.batch.batchFarmCollections.farmCollection.user',
            'lotBatches.batch.user',
            'user',
            'blockchain',
            'flavors',
        ]);

        return Inertia::render('Lot/LotTraceability', $this->lots->traceabilityData($lot));
    }

    /**
     * Download the lot's traceability QR code as a standalone SVG file.
     */
    public function downloadQr(Lot $lot): StreamedResponse
    {
        return response()->streamDownload(
            function () use ($lot): void {
                echo QrCodeHelper::forLotFile($lot);
            },
            ($lot->lot_number ?: 'lot').'-qr.svg',
            ['Content-Type' => 'image/svg+xml']
        );
    }

    /**
     * Show the lot index page.
     */
    public function index(): Response
    {
        return Inertia::render('Lot/LotPage', [
            'lots' => ['data' => [], 'meta' => ['total' => 0, 'current_page' => 1, 'last_page' => 1, 'from' => 0, 'to' => 0]],
            'filters' => [],
        ]);
    }

    /**
     * Store a new buyer lot request.
     */
    public function storeLotRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crop_type' => ['required', 'string', 'max:255'],
            'variety' => ['nullable', 'string', 'max:255'],
            'grade' => ['required', 'string', 'max:255'],
            'amount' => ['nullable'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->lots->createRequest($validated, $request->user()->id);

        return redirect()->back()->with('success', 'Your lot request has been submitted successfully.');
    }

    /**
     * Show the detail page for a single lot request.
     */
    public function showLotRequest(Request $request, LotRequest $lotRequest): Response
    {
        $lotRequest->load('user');

        return Inertia::render('Lot/LotRequestDetails', [
            'lotRequest' => $lotRequest,
            'canEdit' => $request->user()->can('update', $lotRequest),
            'canDelete' => $request->user()->can('delete', $lotRequest),
        ]);
    }

    /**
     * Update an existing lot request (owner or admin only).
     */
    public function updateLotRequest(Request $request, LotRequest $lotRequest): RedirectResponse
    {
        Gate::authorize('update', $lotRequest);

        $data = $request->validate([
            'crop_type' => ['required', 'string', 'max:100'],
            'variety' => ['nullable', 'string', 'max:100'],
            'grade' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->lots->updateRequest($lotRequest, $data);

        return redirect()->route('lot.request.show', $lotRequest)
            ->with('success', 'Lot request updated.');
    }

    /**
     * Delete a lot request (owner or admin only).
     */
    public function destroyLotRequest(LotRequest $lotRequest): RedirectResponse
    {
        Gate::authorize('delete', $lotRequest);

        $this->lots->destroyRequest($lotRequest);

        return redirect()->route('dashboard')
            ->with('success', 'Lot request deleted.');
    }

    /**
     * Show the paginated lot requests index with search and filter support.
     */
    public function lotRequestIndex(Request $request): Response
    {
        return Inertia::render('Lot/LotRequests', [
            'requests' => $this->lots->paginatedRequests($request->only(['search', 'status', 'crop_type'])),
            'filters' => $request->only(['search', 'status', 'crop_type']),
        ]);
    }
}
