<?php

namespace App\Http\Controllers\Harvest;

use App\Http\Controllers\Controller;
use App\Http\Resources\HarvestResource;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\DocumentMetadata;
use App\Models\HarvestDocument;
use App\Models\PickMethodMetadata;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HarvestController extends Controller
{
    /**
     * Display the harvest directory.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search')->value());

        $paginator = Harvest::query()
            ->with('farm')
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereRaw('CAST(id AS CHAR) LIKE ?', ["%{$search}%"]);
            })
            ->latest('harvest_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Harvest $harvest): array => [
                'id' => $harvest->id,
                'code' => self::formatHarvestCode($harvest),
                'farm_name' => $harvest->farm?->name ?? "Farm #{$harvest->farm_id}",
                'date_planted' => $harvest->date_planted?->toDateString(),
                'harvest_date' => $harvest->harvest_date?->toDateString(),
                'harvest_season' => $harvest->harvest_season,
                'status' => $harvest->status,
                'weight' => (float) $harvest->weight,
                'price' => $harvest->price !== null ? (float) $harvest->price : null,
                'pick_method' => $harvest->pick_method,
                'show_url' => route('harvest.show', $harvest),
            ]);

        $harvestCollection = Harvest::query()->get(['id', 'weight', 'ripeness_percentage', 'pick_method', 'harvest_date']);
        $averageQuality = $harvestCollection->count() > 0
            ? round($harvestCollection->avg(fn (Harvest $harvest): float => self::scoreForRipeness($harvest->ripeness_percentage)), 1)
            : 0;
        $processingCount = $harvestCollection
            ->filter(fn (Harvest $harvest): bool => self::statusToneForHarvest($harvest) === 'processing')
            ->count();
        $estateOptions = Harvest::query()
            ->with('farm')
            ->get()
            ->pluck('farm.name')
            ->filter()
            ->unique()
            ->values();

        return Inertia::render('Harvest/HarvestsPage', [
            'harvests' => [
                'data' => array_values($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'stats' => [
                'total_yield' => (float) Harvest::query()->sum('weight'),
                'active_harvests' => Harvest::query()->count(),
                'processing_count' => $processingCount,
                'avg_quality_score' => $averageQuality,
            ],
            'filters' => [
                'search' => $search,
            ],
            'estateOptions' => $estateOptions,
        ]);
    }

    /**
     * Show the harvest creation form.
     */
    public function create(): Response
    {
        Gate::authorize('create', Harvest::class);

        $farms = Farm::query()
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

        return Inertia::render('Harvest/Create', [
            'farmOptions' => $farms,
            'pickMethodOptions' => self::pickMethodOptions(),
            'harvestSeasonOptions' => self::harvestSeasonOptions(),
        ]);
    }

    /**
     * Display the specified harvest profile.
     */
    public function show(Harvest $harvest): Response
    {
        $harvest->load([
            'farm.farmer',
            'creator',
            'documents' => fn ($query) => $query->latest(),
        ]);
        Gate::authorize('view', $harvest);

        return Inertia::render('Harvest/HarvestProfile', [
            'harvest' => HarvestResource::make($harvest)->resolve(),
            'dateRange' => self::getRangeOfDates(
                $harvest->date_planted?->toDateString() ?? '',
                $harvest->harvest_date?->toDateString() ?? '',
            ),
            'pickMethodOptions' => self::pickMethodOptions(),
            'harvestSeasonOptions' => self::harvestSeasonOptions(),
            'documentTypeOptions' => self::documentTypeOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Harvest::class);

        $pickMethodOptions = self::pickMethodOptions()->all();
        $harvestSeasonOptions = self::harvestSeasonOptions();

        $validated = $request->validate(
            [
                'farm_id' => ['required', 'exists:farms,id'],
                'variety' => ['required', 'string', 'max:255'],
                'date_planted' => ['required', 'date', 'before_or_equal:today'],
                'harvest_date' => ['required', 'date', 'before_or_equal:today'],
                'harvest_season' => ['required', 'string', 'max:255', Rule::in($harvestSeasonOptions)],
                'pick_method' => ['required', 'string', 'max:255', Rule::in($pickMethodOptions)],
                'price' => ['required', 'numeric', 'min:0.01'],
                'weight' => ['required', 'numeric', 'min:0.01'],
                'ripeness_percentage' => ['required', 'numeric', 'between:0,100'],
                'foreign_matter_present' => ['required', 'boolean'],
                'pest_damage' => ['required', 'boolean'],
                'disease_signs' => ['required', 'boolean'],
                'visible_defects' => ['required', 'boolean'],
            ],
            [
                'date_planted.before_or_equal' => 'Date planted must not be greater than today.',
                'harvest_date.before_or_equal' => 'Harvest date must not be greater than today.',
                'foreign_matter_present.required' => 'Please confirm whether foreign matter is present.',
                'pest_damage.required' => 'Please confirm whether pest damage is present.',
                'disease_signs.required' => 'Please confirm whether disease signs are present.',
                'visible_defects.required' => 'Please confirm whether visible defects are present.',
            ],
        );

        $harvest = Harvest::query()->create([
            ...$validated,
            'user_id' => $request->user()->id,
            'foreign_matter_present' => $request->boolean('foreign_matter_present'),
            'pest_damage' => $request->boolean('pest_damage'),
            'disease_signs' => $request->boolean('disease_signs'),
            'visible_defects' => $request->boolean('visible_defects'),
        ]);

        return redirect()
            ->route('harvest.show', $harvest)
            ->with('success', "Harvest {$harvest->id} recorded successfully.");
    }

    /**
     * Update the specified harvest quality data.
     */
    public function update(Request $request, Harvest $harvest): RedirectResponse
    {
        Gate::authorize('update', $harvest);

        $pickMethodOptions = self::pickMethodOptions()->all();
        $harvestSeasonOptions = self::harvestSeasonOptions();

        $validated = $request->validate(
            [
                'harvest_date' => ['required', 'date', 'before_or_equal:today'],
                'harvest_season' => ['required', 'string', 'max:255', Rule::in($harvestSeasonOptions)],
                'pick_method' => ['required', 'string', 'max:255', Rule::in($pickMethodOptions)],
                'price' => ['required', 'numeric', 'min:0.01'],
                'weight' => ['required', 'numeric', 'min:0.01'],
                'ripeness_percentage' => ['required', 'numeric', 'between:0,100'],
                'foreign_matter_present' => ['required', 'boolean'],
                'pest_damage' => ['required', 'boolean'],
                'disease_signs' => ['required', 'boolean'],
                'visible_defects' => ['required', 'boolean'],
            ],
            [
                'harvest_date.before_or_equal' => 'Harvest date must not be greater than today.',
                'foreign_matter_present.required' => 'Please confirm whether foreign matter is present.',
                'pest_damage.required' => 'Please confirm whether pest damage is present.',
                'disease_signs.required' => 'Please confirm whether disease signs are present.',
                'visible_defects.required' => 'Please confirm whether visible defects are present.',
            ],
        );

        $harvest->update([
            ...$validated,
            'foreign_matter_present' => $request->boolean('foreign_matter_present'),
            'pest_damage' => $request->boolean('pest_damage'),
            'disease_signs' => $request->boolean('disease_signs'),
            'visible_defects' => $request->boolean('visible_defects'),
        ]);

        return back();
    }

    /**
     * Store a document for the specified harvest.
     */
    public function storeDocument(Request $request, Harvest $harvest): RedirectResponse
    {
        Gate::authorize('update', $harvest);

        $documentTypeOptions = self::documentTypeOptions()->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:255', Rule::in($documentTypeOptions)],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        $file = $request->file('document');
        $path = $file->store('harvest-documents', 'public');

        HarvestDocument::query()->create([
            'harvest_id' => $harvest->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'document_type' => $validated['document_type'] ?? null,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back();
    }
    /**
     * Return the inclusive monthly range buckets between the planted and harvest dates.
     *
     * @return array<int, array{start:string, end:string}>
     */
    public static function getRangeOfDates(string $datePlanted, string $harvestDate): array
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

    protected static function scoreForRipeness(mixed $ripenessPercentage): float
    {
        if ($ripenessPercentage === null || $ripenessPercentage === '') {
            return 82.0;
        }

        return round(max(0, min(100, (float) $ripenessPercentage)), 1);
    }

    protected static function processingMethodFromPickMethod(?string $pickMethod): string
    {
        return match ($pickMethod) {
            'Selective Picking' => 'Washed',
            'Strip Picking' => 'Natural',
            'Mechanical Picking' => 'Honey',
            'Hand Sorting' => 'Anaerobic',
            default => 'Washed',
        };
    }

    protected static function pickMethodOptions()
    {
        return PickMethodMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->values();
    }

    protected static function documentTypeOptions()
    {
        return DocumentMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->values();
    }

    protected static function harvestSeasonOptions(): array
    {
        return [
            'Main Crop',
            'Fly Crop',
            'Early Harvest',
            'Late Harvest',
        ];
    }

    protected static function statusForHarvest(Harvest $harvest): string
    {
        if (!$harvest->harvest_date) {
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

    protected static function statusToneForHarvest(Harvest $harvest): string
    {
        return match (self::statusForHarvest($harvest)) {
            'In Processing' => 'processing',
            'Drying' => 'drying',
            default => 'ready',
        };
    }

    protected static function formatHarvestCode(Harvest $harvest): string
    {
        $year = $harvest->harvest_date?->format('Y') ?? now()->format('Y');

        return "#{$year}-EX" . str_pad((string) $harvest->id, 2, '0', STR_PAD_LEFT);
    }















}
