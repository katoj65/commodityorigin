<?php

namespace App\Http\Controllers\Harvest;

use App\Http\Controllers\Controller;
use App\Http\Resources\HarvestResource;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\PickMethodMetadata;
use App\Models\RipenessGradeMetadata;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HarvestController extends Controller
{
    /**
     * Show the harvest creation form.
     */
    public function create(): Response
    {
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
            'pickMethodOptions' => PickMethodMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->values(),
            'ripenessGradeOptions' => RipenessGradeMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->values(),
        ]);
    }

    /**
     * Display the specified harvest profile.
     */
    public function show(Harvest $harvest): Response
    {
        $harvest->load('farm.farmer');

        return Inertia::render('Harvest/HarvestProfile', [
            'harvest' => HarvestResource::make($harvest)->resolve(),
            'dateRange' => self::getRangeOfDates(
                $harvest->date_planted?->toDateString() ?? '',
                $harvest->harvest_date?->toDateString() ?? '',
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'farm_id' => ['required', 'exists:farms,id'],
                'variety' => ['required', 'string', 'max:255'],
                'date_planted' => ['required', 'date', 'before_or_equal:today'],
                'harvest_date' => ['required', 'date', 'before_or_equal:today'],
                'pick_method' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0.01'],
                'weight' => ['required', 'numeric', 'min:0.01'],
                'ripeness_grade' => ['required', 'string', 'max:255'],
            ],
            [
                'date_planted.before_or_equal' => 'Date planted must not be greater than today.',
                'harvest_date.before_or_equal' => 'Harvest date must not be greater than today.',
            ],
        );

        $harvest = Harvest::query()->create($validated);

        return redirect()
            ->route('harvest.show', $harvest)
            ->with('success', "Harvest {$harvest->id} recorded successfully.");
    }
    /**
     * Return the inclusive list of months between the planted and harvest dates.
     *
     * @return array<int, string>
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

        $months = [];
        $cursor = $start->copy();

        while ($cursor->lessThanOrEqualTo($end)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return $months;
    }















}
