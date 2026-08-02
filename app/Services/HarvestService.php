<?php

namespace App\Services;

use App\Models\DocumentMetadata;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\HarvestDocument;
use App\Models\HarvestSustainability;
use App\Models\PickMethodMetadata;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class HarvestService
{
    /**
     * Get a base query builder for harvests.
     */
    public function query(): Builder
    {
        return Harvest::query();
    }

    /**
     * Paginate harvests for the harvest directory, optionally filtered by
     * an id search term.
     */
    public function paginateForList(string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->with(['farm', 'season'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereRaw('CAST(id AS CHAR) LIKE ?', ["%{$search}%"]);
            })
            ->latest('harvest_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Harvest $harvest): array => $this->harvestListItem($harvest));
    }

    /**
     * Shape a harvest for the harvest directory list.
     *
     * @return array<string, mixed>
     */
    public function harvestListItem(Harvest $harvest): array
    {
        return [
            'id' => $harvest->id,
            'code' => $this->formatHarvestCode($harvest),
            'farm_name' => $harvest->farm?->name ?? "Farm #{$harvest->farm_id}",
            'season_name' => $harvest->season?->name,
            'date_planted' => $harvest->date_planted?->toDateString(),
            'harvest_date' => $harvest->harvest_date?->toDateString(),
            'harvest_season' => $harvest->harvest_season,
            'status' => $harvest->status,
            'weight' => (float) $harvest->weight,
            'price' => $harvest->price !== null ? (float) $harvest->price : null,
            'pick_method' => $harvest->pick_method,
            'show_url' => route('harvest.show', $harvest),
        ];
    }

    /**
     * Get harvests recorded by the given user, farm and creator eager
     * loaded, newest harvest date first.
     */
    public function listForUser(int $userId): Collection
    {
        return $this->query()
            ->with(['farm', 'creator'])
            ->where('user_id', $userId)
            ->latest('harvest_date')
            ->latest('id')
            ->get();
    }

    /**
     * Get every harvest across all farms, for admin oversight.
     */
    public function listAll(): Collection
    {
        return $this->query()
            ->with(['farm', 'creator'])
            ->latest('harvest_date')
            ->latest('id')
            ->get();
    }

    /**
     * Aggregate stats for the harvest directory.
     *
     * @return array<string, mixed>
     */
    public function indexStats(): array
    {
        $harvests = $this->query()->get(['id', 'weight', 'ripeness_percentage', 'pick_method', 'harvest_date']);

        $averageQuality = $harvests->count() > 0
            ? round($harvests->avg(fn (Harvest $harvest): float => $this->scoreForRipeness($harvest->ripeness_percentage)), 1)
            : 0;

        $processingCount = $harvests
            ->filter(fn (Harvest $harvest): bool => $this->statusToneForHarvest($harvest) === 'processing')
            ->count();

        return [
            'total_yield' => (float) $this->query()->sum('weight'),
            'active_harvests' => $this->query()->count(),
            'processing_count' => $processingCount,
            'avg_quality_score' => $averageQuality,
        ];
    }

    /**
     * Distinct farm names that have recorded harvests, for filter dropdowns.
     */
    public function estateOptions(): Collection
    {
        return $this->query()
            ->with('farm')
            ->get()
            ->pluck('farm.name')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Farm options for the harvest creation form.
     */
    public function farmOptionsForCreate(): Collection
    {
        return Farm::query()
            ->with('farmer')
            ->orderBy('name')
            ->get()
            ->map(fn (Farm $farm): array => [
                'id' => $farm->id,
                'name' => $farm->name,
                'location' => $farm->location,
                'variety' => $farm->variety,
            ])
            ->values();
    }

    /**
     * Create a new harvest record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Harvest
    {
        return Harvest::query()->create($data);
    }

    /**
     * Update an existing harvest's quality data.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Harvest $harvest, array $data): Harvest
    {
        $harvest->update($data);

        return $harvest;
    }

    /**
     * Delete a harvest record.
     */
    public function delete(Harvest $harvest): void
    {
        $harvest->delete();
    }

    /**
     * Store an uploaded document against a harvest.
     *
     * @param  array<string, mixed>  $data
     */
    public function storeDocument(Harvest $harvest, UploadedFile $file, array $data, int $userId): HarvestDocument
    {
        $path = $file->store('harvest-documents', 'public');

        return HarvestDocument::query()->create([
            'harvest_id' => $harvest->id,
            'user_id' => $userId,
            'title' => $data['title'],
            'document_type' => $data['document_type'] ?? null,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Store or update sustainability details for a harvest.
     *
     * @param  array<string, mixed>  $data
     */
    public function storeSustainability(Harvest $harvest, array $data, int $userId): HarvestSustainability
    {
        return HarvestSustainability::query()->updateOrCreate(
            ['harvest_id' => $harvest->id],
            [
                'user_id' => $userId,
                'organic_certified' => $data['organicCertified'],
                'climate_smart' => $data['climateSmart'],
                'shade_grown' => $data['shadeGrown'],
                'water_management' => $data['waterManagement'],
                'soil_conservation' => $data['soilConservation'],
                'low_carbon' => $data['lowCarbon'],
                'fair_wages' => $data['fairWages'],
                'notes' => $data['notes'] ?? null,
            ],
        );
    }

    /**
     * Return the inclusive monthly range buckets between the planted and harvest dates.
     *
     * @return array<int, array{start:string, end:string}>
     */
    public function getRangeOfDates(string $datePlanted, string $harvestDate): array
    {
        if ($datePlanted === '' || $harvestDate === '') {
            return [];
        }

        $start = Carbon::parse($datePlanted)->startOfMonth();
        $end = Carbon::parse($harvestDate)->startOfMonth();

        if ($start->greaterThan($end)) {
            return [];
        }

        $totalMonths = $start->diffInMonths($end) + 1;
        $interval = $totalMonths > 12 ? 6 : 1;
        $ranges = [];
        $cursor = $start->copy();

        while ($cursor->lessThanOrEqualTo($end)) {
            $rangeStart = $cursor->copy();
            $rangeEnd = $interval === 1
                ? $cursor->copy()
                : $cursor->copy()->addMonths($interval - 1)->startOfMonth();

            if ($rangeEnd->greaterThan($end)) {
                $rangeEnd = $end->copy();
            }

            $ranges[] = [
                'start' => $rangeStart->format('Y-m'),
                'end' => $rangeEnd->format('Y-m'),
            ];

            $cursor->addMonths($interval);
        }

        return $ranges;
    }

    public function scoreForRipeness(mixed $ripenessPercentage): float
    {
        if ($ripenessPercentage === null || $ripenessPercentage === '') {
            return 82.0;
        }

        return round(max(0, min(100, (float) $ripenessPercentage)), 1);
    }

    public function processingMethodFromPickMethod(?string $pickMethod): string
    {
        return match ($pickMethod) {
            'Selective Picking' => 'Washed',
            'Strip Picking' => 'Natural',
            'Mechanical Picking' => 'Honey',
            'Hand Sorting' => 'Anaerobic',
            default => 'Washed',
        };
    }

    public function pickMethodOptions(): Collection
    {
        return PickMethodMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->values();
    }

    public function documentTypeOptions(): Collection
    {
        return DocumentMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->values();
    }

    public function harvestSeasonOptions(): array
    {
        return [
            'Main Crop',
            'Fly Crop',
            'Early Harvest',
            'Late Harvest',
        ];
    }

    public function statusForHarvest(Harvest $harvest): string
    {
        if (! $harvest->harvest_date) {
            return 'Queued';
        }

        $daysSinceHarvest = Carbon::parse($harvest->harvest_date)->diffInDays(now());

        if ($daysSinceHarvest <= 7) {
            return 'In Processing';
        }

        if ($daysSinceHarvest <= 21) {
            return 'Drying';
        }

        return 'Ready for Export';
    }

    public function statusToneForHarvest(Harvest $harvest): string
    {
        return match ($this->statusForHarvest($harvest)) {
            'In Processing' => 'processing',
            'Drying' => 'drying',
            default => 'ready',
        };
    }

    public function formatHarvestCode(Harvest $harvest): string
    {
        $year = $harvest->harvest_date?->format('Y') ?? now()->format('Y');

        return "#{$year}-EX" . str_pad((string) $harvest->id, 2, '0', STR_PAD_LEFT);
    }
}
