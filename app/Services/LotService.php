<?php

namespace App\Services;

use App\Helpers\ImageUploadHelper;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\Lot;
use App\Models\LotRequest;
use App\Models\Market;
use App\Models\MarketMetadata;
use App\Models\ProcessingMetadata;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class LotService
{
    /**
     * Get a base query builder for lots.
     */
    public function query(): Builder
    {
        return Lot::query();
    }

    /**
     * Create a legacy, batch-picker lot.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image, int $userId): Lot
    {
        return Lot::query()->create([
            ...$data,
            'image' => ImageUploadHelper::store($image, 'lots'),
            'net_weight_kg' => round((float) $data['quantity_bags'] * (float) $data['bag_weight_kg'], 2),
            'user_id' => $userId,
            'status' => 'draft',
        ]);
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
                'batch' => 'This batch must be linked to at least one harvest farm before a lot can be created.',
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

        $sensoryAverage = round(collect([
            $data['aroma_score'],
            $data['acidity_score'],
            $data['body_score'],
        ])->avg(), 2);

        $lot = Lot::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $userId,
            'lot_number' => $data['lot_number'],
            'lot_name' => $data['lot_name'] ?? null,
            'description' => $data['description'] ?? null,
            'image' => ImageUploadHelper::store($image, 'lots'),
            'process' => $data['process'],
            'grade' => $data['grade'],
            'quantity_bags' => $data['quantity_bags'],
            'bag_weight_kg' => $data['bag_weight_kg'],
            'packaging_type' => $data['packaging_type'],
            'net_weight_kg' => round((float) $data['allocation_kg'], 2),
            'price' => $data['price_per_kg'],
            'quality_score' => $batch->cup_score ?: $sensoryAverage,
            'status' => $this->resolveLotStatus($data['submission_intent'] ?? 'create'),
            'notes' => $data['notes'] ?? null,
        ]);

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
        $batch->loadMissing(['season', 'ownerships', 'lots']);

        $sourceFarm = $this->batchHarvests($batch)->first()?->farm;
        $allocatedQtyKg = $this->allocatedQuantityKg($batch);
        $remainingQtyKg = max(round((float) $batch->weight - $allocatedQtyKg, 2), 0.0);

        return [
            'source_farm' => $sourceFarm,
            'allocated_kg' => $allocatedQtyKg,
            'remaining_kg' => $remainingQtyKg,
        ];
    }

    /**
     * Resolve harvests linked to the batch.
     *
     * @return Collection<int, Harvest>
     */
    private function batchHarvests(Batch $batch): Collection
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
    public function processingMethodOptions(): Collection
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
    public function targetMarketOptions(): Collection
    {
        return MarketMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Shape the batch list for the legacy lot creation form's picker.
     *
     * @return array<int, array<string, mixed>>
     */
    public function batchSummaries(): array
    {
        return Batch::query()
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
            ->values()
            ->all();
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
     * Publish a lot to the live market. Returns false if it's already
     * published rather than throwing, so the controller can surface a
     * plain "already published" message.
     */
    public function publish(Lot $lot, int $userId): bool
    {
        if (Market::where('lot_id', $lot->id)->exists()) {
            return false;
        }

        $lot->loadMissing('batch');

        Market::create([
            'lot_id' => $lot->id,
            'user_id' => $userId,
            'lot_code' => $lot->lot_number,
            'name' => $lot->lot_name ?? $lot->lot_number,
            'origin' => $lot->batch?->warehouse_location,
            'type' => $lot->batch?->variety ?? 'Arabica',
            'process' => $lot->process,
            'quality_score' => $lot->quality_score,
            'quantity' => $lot->net_weight_kg,
            'price_per_kg' => $lot->price,
            'target_market' => $lot->target_market,
            'status' => 'live',
            'image' => $lot->image,
        ]);

        return true;
    }

    /**
     * Shape a lot, its batch, and its season into the traceability
     * timeline page's payload.
     *
     * @return array<string, mixed>
     */
    public function traceabilityData(Lot $lot): array
    {
        $lot->loadMissing(['batch.season']);

        $batch = $lot->batch;
        $season = $batch?->season;

        return [
            'lot' => [
                'id' => $lot->id,
                'lot_number' => $lot->lot_number,
                'lot_name' => $lot->lot_name,
                'status' => $lot->status,
                'process' => $lot->process,
                'grade' => $lot->grade,
                'net_weight_kg' => (float) ($lot->net_weight_kg ?? 0),
                'quality_score' => (float) ($lot->quality_score ?? 0),
                'price_per_kg' => (float) ($lot->price ?? 0),
                'packaging_type' => $lot->packaging_type,
                'created_at' => $lot->created_at?->format('d M Y'),
                'image' => $lot->image,
            ],
            'batch' => $batch ? [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'variety' => $batch->variety,
                'processing_method' => $batch->processing_method,
                'moisture_content' => (float) ($batch->moisture_content ?? 0),
                'cup_score' => (float) ($batch->cup_score ?? 0),
                'weight' => (float) ($batch->weight ?? 0),
                'warehouse_location' => $batch->warehouse_location,
            ] : null,
            'season' => $season ? [
                'id' => $season->id,
                'name' => $season->name,
                'start_date' => $season->start_date?->format('d M Y'),
                'end_date' => $season->end_date?->format('d M Y'),
                'status' => $season->status,
            ] : null,
        ];
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
