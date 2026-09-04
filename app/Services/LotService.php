<?php

namespace App\Services;

use App\Helpers\ImageUploadHelper;
use App\Helpers\QrCodeHelper;
use App\Http\Resources\BlockchainResource;
use App\Models\Batch;
use App\Models\CropVarietyMetadata;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\AcidityMetadata;
use App\Models\AftertasteMetadata;
use App\Models\AromaMetadata;
use App\Models\BodyMetadata;
use App\Models\FlavorMetadata;
use App\Models\Lot;
use App\Models\LotBatch;
use App\Models\LotRequest;
use App\Models\Market;
use App\Models\ProcessingMetadata;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LotService
{
    public function __construct(
        private readonly BatchService $batches,
        private readonly BlockchainService $blockchain,
        private readonly MarketImageService $marketImages,
    ) {
    }

    /**
     * Get a base query builder for lots.
     */
    public function query(): Builder
    {
        return Lot::query();
    }

    /**
     * Create a lot from the simple creation form. The lot number is always
     * generated server-side; linking to a batch happens afterward, from
     * the lot profile page, via attachBatch(). Every lot created here gets
     * its traceability QR code and its blockchain commit before returning.
     * `flavor_ids`, if present, is synced onto the lot's flavor-notes
     * many-to-many relation rather than mass-assigned as a plain column.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image, int $userId): Lot
    {
        $flavorIds = $data['flavor_ids'] ?? null;
        unset($data['flavor_ids']);

        $lot = Lot::query()->create([
            ...$data,
            'lot_number' => $this->generateLotNumber(),
            'image' => ImageUploadHelper::store($image, 'lots'),
            'net_weight_kg' => round((float) $data['quantity_bags'] * (float) $data['bag_weight_kg'], 2),
            'user_id' => $userId,
            'status' => 'draft',
        ]);

        if ($flavorIds !== null) {
            $lot->flavors()->sync($flavorIds);
        }

        $this->blockchain->commitLot($lot, $userId);

        return $this->ensureQrCode($lot);
    }

    /**
     * Ensure a lot has a traceability QR code, generating and persisting
     * one when it is missing. Lots created before QR support existed (or
     * with model events bypassed) get backfilled the first time they are
     * viewed.
     */
    public function ensureQrCode(Lot $lot): Lot
    {
        if (! $lot->qr_code) {
            $lot->forceFill(['qr_code' => QrCodeHelper::forLot($lot)])->saveQuietly();
        }

        return $lot;
    }

    /**
     * Update a lot from validated payload data. A new image replaces the
     * existing one; omitting it leaves the current image untouched.
     * `flavor_ids`, if present, is synced onto the lot's flavor-notes
     * many-to-many relation rather than mass-assigned as a plain column.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Lot $lot, array $validated, ?UploadedFile $image = null): Lot
    {
        $flavorIds = $validated['flavor_ids'] ?? null;
        unset($validated['flavor_ids']);

        $lot->update([
            ...$validated,
            'image' => $image ? ImageUploadHelper::store($image, 'lots') : $lot->image,
        ]);

        if ($flavorIds !== null) {
            $lot->flavors()->sync($flavorIds);
        }

        return $lot;
    }

    /**
     * Delete a lot.
     */
    public function destroy(Lot $lot): void
    {
        $lot->delete();
    }

    /**
     * Generate a unique, human-readable lot number (e.g. LOT-2026-AB12CD).
     */
    private function generateLotNumber(): string
    {
        do {
            $number = sprintf('LOT-%d-%s', now()->year, strtoupper(Str::random(6)));
        } while (Lot::query()->where('lot_number', $number)->exists());

        return $number;
    }

    /**
     * Link a lot to a batch via the lot_batch pivot table, denormalizing
     * the batch's number and snapshotting the lot's own net weight as the
     * allocation drawn from that batch.
     */
    public function attachBatch(Lot $lot, Batch $batch, int $userId): LotBatch
    {
        return LotBatch::query()->create([
            'lot_id' => $lot->id,
            'batch_id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'allocation_kg' => $lot->net_weight_kg,
            'user_id' => $userId,
        ]);
    }

    /**
     * Resolve a batch by its batch_number and link it to a lot — used by
     * the "Attach Batch" modal on the lot profile page.
     */
    public function attachBatchByNumber(Lot $lot, string $batchNumber, int $userId): LotBatch
    {
        $batch = $this->batches->findByNumber($batchNumber);

        if (! $batch) {
            throw ValidationException::withMessages([
                'batch_number' => 'No batch with that number was found.',
            ]);
        }

        $alreadyLinked = LotBatch::query()
            ->where('lot_id', $lot->id)
            ->where('batch_id', $batch->id)
            ->exists();

        if ($alreadyLinked) {
            throw ValidationException::withMessages([
                'batch_number' => 'This batch is already linked to this lot.',
            ]);
        }

        return $this->attachBatch($lot, $batch, $userId);
    }

    /**
     * Create a lot allocated from a specific batch — validates the
     * allocation against the batch's remaining (unallocated) quantity and
     * requires the batch to have a resolvable source farm.
     *
     * @param  array<string, mixed>  $data
     * @return array{lot: Lot, fully_allocated: bool}
     */
    public function createFromBatch(array $data, ?UploadedFile $image, Batch $batch, int $userId): array
    {
        $metrics = $this->batchMetrics($batch);

        if (! $metrics['source_farm']) {
            throw ValidationException::withMessages([
                'batch' => 'This batch must be linked to at least one farm collection before a lot can be created.',
            ]);
        }

        if ($metrics['remaining_kg'] <= 0) {
            throw ValidationException::withMessages([
                'allocation_kg' => 'This batch is fully allocated. No remaining quantity is available for a new lot.',
            ]);
        }

        if ((float) $data['allocation_kg'] > (float) $batch->weight) {
            throw ValidationException::withMessages([
                'allocation_kg' => sprintf(
                    'Lot allocation cannot exceed the total batch weight of %.2f kg.',
                    (float) $batch->weight
                ),
            ]);
        }

        if ((float) $data['allocation_kg'] > $metrics['remaining_kg']) {
            throw ValidationException::withMessages([
                'allocation_kg' => sprintf(
                    'Lot allocation cannot exceed the remaining batch quantity of %.2f kg.',
                    $metrics['remaining_kg']
                ),
            ]);
        }

        $lot = Lot::query()->create([
            'user_id' => $userId,
            'lot_number' => $data['lot_number'],
            'lot_name' => $data['lot_name'] ?? null,
            'description' => $data['description'] ?? null,
            'image' => ImageUploadHelper::store($image, 'lots'),
            'process' => $data['process'],
            'grade' => $data['grade'],
            'variety' => $data['variety'] ?? null,
            'origin' => $data['origin'] ?? null,
            'region' => $data['region'] ?? null,
            'year_of_harvest' => $data['year_of_harvest'] ?? null,
            'moisture' => $data['moisture'] ?? null,
            'defects_percentage' => $data['defects_percentage'] ?? null,
            'screen' => $data['screen'] ?? null,
            'quantity_bags' => $data['quantity_bags'],
            'bag_weight_kg' => $data['bag_weight_kg'],
            'packaging_type' => $data['packaging_type'],
            'net_weight_kg' => round((float) $data['allocation_kg'], 2),
            'price' => $data['price'] ?? null,
            'quality_score' => $data['quality_score'] ?? $batch->cup_score,
            'acidity' => $data['acidity'] ?? null,
            'body' => $data['body'] ?? null,
            'flavor' => $data['flavor'] ?? null,
            'aroma' => $data['aroma'] ?? null,
            'balance' => $data['balance'] ?? null,
            'aftertaste' => $data['aftertaste'] ?? null,
            'status' => $this->resolveLotStatus($data['submission_intent'] ?? 'create'),
            'notes' => $data['notes'] ?? null,
        ]);

        if (! empty($data['flavor_ids'])) {
            $lot->flavors()->sync($data['flavor_ids']);
        }

        $this->attachBatch($lot, $batch, $userId);

        $this->blockchain->commitLot($lot, $userId);

        // Guarantee the traceability QR code link is stored on the lot row.
        $this->ensureQrCode($lot);

        return [
            'lot' => $lot,
            'fully_allocated' => $metrics['remaining_kg'] - (float) $data['allocation_kg'] <= 0,
        ];
    }

    /**
     * Resolve the source farm plus current allocation numbers for a batch.
     *
     * @return array{source_farm: ?Farm, allocated_kg: float, remaining_kg: float}
     */
    public function batchMetrics(Batch $batch): array
    {
        $batch->loadMissing(['batchFarmCollections.farmCollection.farm', 'lotBatches']);

        $sourceFarm = $this->batchSourceFarm($batch);
        $allocatedQtyKg = $this->allocatedQuantityKg($batch);
        $remainingQtyKg = max(round((float) $batch->weight - $allocatedQtyKg, 2), 0.0);

        return [
            'source_farm' => $sourceFarm,
            'allocated_kg' => $allocatedQtyKg,
            'remaining_kg' => $remainingQtyKg,
        ];
    }

    /**
     * Resolve the farm a batch was sourced from, via the
     * batch_farm_collection pivot table (a batch may draw from more than
     * one farm collection; the first linked collection's farm is treated
     * as the primary source).
     */
    private function batchSourceFarm(Batch $batch): ?Farm
    {
        return $batch->batchFarmCollections
            ->first(fn ($link) => $link->farmCollection?->farm)
            ?->farmCollection
            ?->farm;
    }

    /**
     * Calculate the quantity already allocated to lots from this batch,
     * via the lot_batch pivot table's allocation_kg column.
     */
    private function allocatedQuantityKg(Batch $batch): float
    {
        $lotBatches = $batch->relationLoaded('lotBatches')
            ? $batch->lotBatches
            : $batch->lotBatches()->get();

        return round((float) $lotBatches->sum('allocation_kg'), 2);
    }

    /**
     * Fetch active processing methods from metadata.
     *
     * @return Collection<int, ProcessingMetadata>
     */
    public function processingMethodOptions(): Collection
    {
        return ProcessingMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Fetch active crop varieties from metadata.
     *
     * @return Collection<int, CropVarietyMetadata>
     */
    public function varietyOptions(): Collection
    {
        return CropVarietyMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Active flavor-note options for the lot cupping-profile forms.
     */
    public function activeFlavorOptions(): Collection
    {
        return FlavorMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);
    }

    /**
     * Resolve a flavor_metadata slug to its display name, if it still exists.
     */
    private function flavorNameFor(string $slug): ?string
    {
        return FlavorMetadata::query()->where('slug', $slug)->value('name');
    }

    /**
     * Active body options for the lot cupping-profile forms.
     */
    public function activeBodyOptions(): Collection
    {
        return BodyMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);
    }

    /**
     * Resolve a body_metadata slug to its display name, if it still exists.
     */
    private function bodyNameFor(string $slug): ?string
    {
        return BodyMetadata::query()->where('slug', $slug)->value('name');
    }

    /**
     * Active acidity options for the lot cupping-profile forms.
     */
    public function activeAcidityOptions(): Collection
    {
        return AcidityMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);
    }

    /**
     * Resolve an acidity_metadata slug to its display name, if it still exists.
     */
    private function acidityNameFor(string $slug): ?string
    {
        return AcidityMetadata::query()->where('slug', $slug)->value('name');
    }

    /**
     * Active aftertaste options for the lot cupping-profile forms.
     */
    public function activeAftertasteOptions(): Collection
    {
        return AftertasteMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);
    }

    /**
     * Resolve an aftertaste_metadata slug to its display name, if it still exists.
     */
    private function aftertasteNameFor(string $slug): ?string
    {
        return AftertasteMetadata::query()->where('slug', $slug)->value('name');
    }

    /**
     * Active aroma options for the lot cupping-profile forms.
     */
    public function activeAromaOptions(): Collection
    {
        return AromaMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);
    }

    /**
     * Resolve an aroma_metadata slug to its display name, if it still exists.
     */
    private function aromaNameFor(string $slug): ?string
    {
        return AromaMetadata::query()->where('slug', $slug)->value('name');
    }

    /**
     * Resolve a lot's six cupping fields to their display values — the
     * metadata-backed ones (acidity/body/flavor/aroma/aftertaste) to
     * their names, balance to a plain number. Shared by anywhere that
     * needs a lot's sensory profile (traceability, product listings)
     * so the slug→name lookups aren't re-derived per caller.
     *
     * @return array<string, mixed>
     */
    public function cuppingProfileFor(?Lot $lot): array
    {
        return [
            'acidity' => $lot?->acidity ? ($this->acidityNameFor($lot->acidity) ?? $lot->acidity) : null,
            'body' => $lot?->body ? ($this->bodyNameFor($lot->body) ?? $lot->body) : null,
            'flavor' => $lot?->flavor ? ($this->flavorNameFor($lot->flavor) ?? $lot->flavor) : null,
            'aroma' => $lot?->aroma ? ($this->aromaNameFor($lot->aroma) ?? $lot->aroma) : null,
            'balance' => $this->toFloat($lot?->balance),
            'aftertaste' => $lot?->aftertaste ? ($this->aftertasteNameFor($lot->aftertaste) ?? $lot->aftertaste) : null,
        ];
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

    /**
     * Publish a lot to the live market. The listing's sellable fields
     * (title, quantity, price, etc.) come from the validated form data —
     * the caller pre-fills it from the lot, but may adjust it before
     * publishing (e.g. listing less than the lot's full net weight).
     * Returns false if it's already published rather than throwing, so
     * the controller can surface a plain "already published" message.
     *
     * @param  array<string, mixed>  $data
     */
    public function publish(Lot $lot, User $user, array $data): bool
    {
        if (Market::where('lot_id', $lot->id)->exists()) {
            return false;
        }

        Market::create([
            'lot_id' => $lot->id,
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'quantity' => $data['quantity'],
            'available_quantity' => $data['available_quantity'] ?? $data['quantity'],
            'unit' => $data['unit'] ?? 'kg',
            'currency' => $data['currency'] ?? 'USD',
            'price_per_unit' => $data['price_per_unit'],
            'pricing_type' => $data['pricing_type'] ?? 'fixed',
            'minimum_order_quantity' => $data['minimum_order_quantity'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'delivery_terms' => $data['delivery_terms'] ?? null,
            'delivery_location' => $data['delivery_location'] ?? null,
            'available_from' => $data['available_from'] ?? null,
            'delivery_method' => $data['delivery_method'] ?? null,
            'incoterm' => $data['incoterm'] ?? null,
            'dispatch' => $data['dispatch'] ?? null,
            'transport_arrangement' => $data['transport_arrangement'] ?? null,
            'insurance_arrangement' => $data['insurance_arrangement'] ?? null,
            'status' => 'live',
            'is_featured' => $data['is_featured'] ?? false,
            'is_public' => $data['is_public'] ?? true,
            // origin/type/process/quality_score/image have no dedicated
            // column on this general-purpose listing shape — kept in
            // metadata; Market's accessors read them back out under their
            // old names for MarketService's filtering/analytics.
            'metadata' => array_filter([
                'origin' => $lot->origin,
                'type' => $lot->variety ?: 'Arabica',
                'process' => $lot->process,
                'quality_score' => $lot->quality_score,
                'image' => $lot->image,
            ], fn ($value) => $value !== null),
        ]);

        return true;
    }

    /**
     * Remove a lot's live market listing. Gallery photos are deleted first
     * (mirroring MarketController::destroy()) so nothing orphans in
     * storage; a lot with no listing is a no-op.
     */
    public function unpublish(Lot $lot): void
    {
        $market = Market::where('lot_id', $lot->id)->first();

        if (! $market) {
            return;
        }

        foreach ($market->images as $image) {
            $this->marketImages->delete($image);
        }

        $market->delete();
    }

    /**
     * Resolve the batch a lot was primarily sourced from, via the
     * lot_batch pivot table (a lot may link to more than one batch; the
     * first linked batch is treated as the primary source for display).
     */
    public function primaryBatch(Lot $lot): ?Batch
    {
        return $lot->lotBatches()->with('batch')->first()?->batch;
    }

    /**
     * Shape the full traceability payload for a lot: the lot itself, every
     * batch it draws from, and for each batch the farm collections, farms,
     * and farmers behind it, plus the users who recorded each step. A
     * chronological `timeline` is assembled for the journey visualisation,
     * alongside aggregate `stats` for the summary strip.
     *
     * Expects the lot to be loaded with:
     * lotBatches.batch.batchFarmCollections.farmCollection.farm.farmers,
     * lotBatches.batch.user, user.
     *
     * @return array<string, mixed>
     */
    public function traceabilityData(Lot $lot): array
    {
        $this->ensureQrCode($lot);

        $timeline = [];
        $allCollections = collect();
        $allFarmers = collect();
        $allFarms = collect();
        $farmIds = [];

        $batches = $lot->lotBatches
            ->map(function (LotBatch $lotBatch) use (&$timeline, &$allCollections, &$allFarmers, &$allFarms, &$farmIds): ?array {
                $batch = $lotBatch->batch;

                if (! $batch) {
                    return null;
                }

                $collections = $batch->batchFarmCollections
                    ->map(fn ($link) => $this->formatCollection($link, $batch))
                    ->filter()
                    ->values();

                $farms = $collections->pluck('farm')->filter()->unique('id')->values()->all();
                $farmers = $collections->flatMap(fn ($c) => $c['farm']['farmers'] ?? [])->unique('id')->values()->all();

                foreach ($collections as $collection) {
                    $allCollections->push($collection);
                    if ($collection['farm']) {
                        $farmIds[$collection['farm']['id']] = true;
                        $allFarms->push($collection['farm']);
                        foreach ($collection['farm']['farmers'] ?? [] as $farmer) {
                            $allFarmers->push($farmer);
                        }
                    }
                    $timeline[] = [
                        'stage' => 'collection',
                        'date' => $collection['collection_date'] ?? $collection['created_at'],
                        'title' => $collection['farm'] ? 'Collected at '.$collection['farm']['name'] : 'Farm collection '.$collection['collection_code'],
                        'subtitle' => $collection['collection_code'],
                        'href' => route('farm-collection.show', $collection['id']),
                    ];
                }

                $timeline[] = [
                    'stage' => 'batching',
                    'date' => $batch->processing_date?->toDateString() ?? $batch->created_at?->toDateString(),
                    'title' => 'Batch '.$batch->batch_number.' processed',
                    'subtitle' => $batch->processing_method,
                    'href' => route('batch.show', $batch->id),
                ];

                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'variety' => $batch->variety,
                    'processing_method' => $batch->processing_method,
                    'drying_method' => $batch->drying_method,
                    'moisture_content' => $this->toFloat($batch->moisture_content),
                    'cup_score' => $this->toFloat($batch->cup_score),
                    'weight_kg' => $this->toFloat($batch->weight),
                    'screen_size' => $batch->screen_size,
                    'warehouse_location' => $batch->warehouse_location,
                    'processing_date' => $batch->processing_date?->format('d M Y'),
                    'status' => $batch->status,
                    'recorded_by' => $this->person($batch->user),
                    'allocation_kg' => $this->toFloat($lotBatch->allocation_kg),
                    'collections' => $collections->all(),
                    'farms' => $farms,
                    'farmers' => $farmers,
                ];
            })
            ->filter()
            ->values();

        $timeline[] = [
            'stage' => 'lotting',
            'date' => $lot->created_at?->toDateString(),
            'title' => 'Lot '.$lot->lot_number.' created',
            'subtitle' => $lot->process,
            'href' => route('lot.show', $lot->id),
        ];

        if ($lot->blockchain) {
            $timeline[] = [
                'stage' => 'blockchain',
                'date' => $lot->blockchain->committed_at?->toDateString() ?? $lot->blockchain->created_at?->toDateString(),
                'title' => 'Committed to the blockchain',
                'subtitle' => 'Block #'.$lot->blockchain->block_number,
                'href' => null,
            ];
        }

        // Order the journey oldest → newest so it reads as a forward trace.
        $timeline = collect($timeline)
            ->filter(fn ($e) => ! empty($e['date']))
            ->sortBy(fn ($e) => strtotime((string) $e['date']) ?: 0)
            ->values()
            ->all();

        return [
            'lot' => [
                'id' => $lot->id,
                'lot_number' => $lot->lot_number,
                'lot_name' => $lot->lot_name,
                'description' => $lot->description,
                'status' => $lot->status,
                'process' => $lot->process,
                'grade' => $lot->grade,
                'variety' => $lot->variety,
                'origin' => $lot->origin,
                'region' => $lot->region,
                'altitude' => $this->toFloat($lot->altitude),
                'year_of_harvest' => $lot->year_of_harvest,
                'moisture' => $this->toFloat($lot->moisture),
                'defects_percentage' => $this->toFloat($lot->defects_percentage),
                'screen' => $lot->screen,
                'net_weight_kg' => $this->toFloat($lot->net_weight_kg),
                'quantity_bags' => $lot->quantity_bags,
                'bag_weight_kg' => $this->toFloat($lot->bag_weight_kg),
                'quality_score' => $this->toFloat($lot->quality_score),
                ...$this->cuppingProfileFor($lot),
                'flavors' => $lot->relationLoaded('flavors') ? $lot->flavors->pluck('name')->all() : [],
                'price' => $this->toFloat($lot->price),
                'currency' => $lot->currency,
                'packaging_type' => $lot->packaging_type,
                'image' => $lot->image,
                'created_at' => $lot->created_at?->format('d M Y'),
                'qr_code' => $lot->qr_code,
                'qr_url' => QrCodeHelper::lotUrl($lot),
                'recorded_by' => $this->person($lot->user),
            ],
            'blockchain' => $lot->blockchain ? BlockchainResource::make($lot->blockchain)->resolve() : null,
            'batches' => $batches->all(),
            'farms' => $allFarms->unique('id')->values()->all(),
            'timeline' => $timeline,
            'stats' => [
                'batches' => $batches->count(),
                'collections' => $allCollections->count(),
                'farms' => count($farmIds),
                'farmers' => $allFarmers->unique('id')->count(),
            ],
        ];
    }

    /**
     * Format a single farm-collection link (from the batch_farm_collection
     * pivot) together with its farm and that farm's farmers.
     *
     * @return array<string, mixed>|null
     */
    private function formatCollection($link, Batch $batch): ?array
    {
        $collection = $link->farmCollection;

        if (! $collection) {
            return null;
        }

        return [
            'id' => $collection->id,
            'collection_code' => $collection->collection_code,
            'collection_date' => $collection->collection_date?->format('d M Y'),
            'coffee_type' => $collection->coffee_type,
            'variety' => $collection->variety,
            'harvest_season' => $collection->harvest_season,
            'quantity' => $this->toFloat($collection->quantity),
            'unit' => $collection->unit,
            'initial_grade' => $collection->initial_grade,
            'initial_moisture' => $this->toFloat($collection->initial_moisture),
            'initial_quality_score' => $this->toFloat($collection->initial_quality_score),
            'status' => $collection->status,
            'recorded_by' => $this->person($collection->user),
            'created_at' => $collection->created_at?->toDateString(),
            'batch_number' => $batch->batch_number,
            'farm' => $this->formatFarm($collection->farm),
        ];
    }

    /**
     * Format a farm with its location, agronomy metadata, and farmers.
     *
     * @return array<string, mixed>|null
     */
    private function formatFarm(?Farm $farm): ?array
    {
        if (! $farm) {
            return null;
        }

        return [
            'id' => $farm->id,
            'name' => $farm->name,
            'farm_code' => $farm->farm_code,
            'country' => $farm->country,
            'region' => $farm->region,
            'district' => $farm->district,
            'village' => $farm->village,
            'location' => $this->farmLocation($farm),
            'latitude' => $farm->latitude,
            'longitude' => $farm->longitude,
            'elevation_m' => $farm->elevation !== null ? (int) round($farm->elevation) : null,
            'coffee_area_ha' => $farm->coffee_area,
            'coffee_type' => $farm->coffee_type,
            'soil_type' => $farm->soil_type,
            'farmers' => $farm->farmers->map(fn (Farmer $farmer): array => [
                'id' => $farmer->id,
                'name' => trim($farmer->first_name.' '.$farmer->last_name),
                'farmer_number' => $farmer->farmer_number,
                'district' => $farmer->district,
                'tel' => $farmer->tel,
            ])->all(),
        ];
    }

    /**
     * Build a human-readable "District, Region, Country" location string.
     */
    private function farmLocation(Farm $farm): string
    {
        return collect([$farm->district, $farm->region, $farm->country])
            ->filter()
            ->implode(', ');
    }

    /**
     * Format a user as a simple {id, name} recorder reference.
     *
     * @return array{id: int, name: string}|null
     */
    private function person($user): ?array
    {
        return $user ? ['id' => $user->id, 'name' => $user->name] : null;
    }

    /**
     * Cast a nullable decimal-ish value to a float (or null).
     */
    private function toFloat($value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * Get a base query builder for lot requests.
     */
    public function requestsQuery(): Builder
    {
        return LotRequest::query();
    }

    /**
     * Get a paginated, searchable/filterable list of lot requests.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginatedRequests(array $filters): LengthAwarePaginator
    {
        return LotRequest::with('user')
            ->when($filters['search'] ?? null, function (Builder $q, string $s): void {
                $q->where('crop_type', 'like', "%{$s}%")
                    ->orWhere('variety', 'like', "%{$s}%")
                    ->orWhere('grade', 'like', "%{$s}%");
            })
            ->when($filters['status'] ?? null, fn (Builder $q, string $s) => $q->where('status', $s))
            ->when($filters['crop_type'] ?? null, fn (Builder $q, string $c) => $q->where('crop_type', $c))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Submit a new lot request.
     *
     * @param  array<string, mixed>  $data
     */
    public function createRequest(array $data, int $userId): LotRequest
    {
        return LotRequest::create([...$data, 'user_id' => $userId]);
    }

    /**
     * Update an existing lot request.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateRequest(LotRequest $lotRequest, array $data): LotRequest
    {
        $lotRequest->update($data);

        return $lotRequest;
    }

    /**
     * Delete a lot request.
     */
    public function destroyRequest(LotRequest $lotRequest): void
    {
        $lotRequest->delete();
    }
}
