<?php

namespace App\Http\Controllers\Season;

use App\Http\Controllers\Controller;
use App\Http\Resources\HarvestResource;
use App\Http\Resources\SeasonResource;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\PickMethodMetadata;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SeasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $paginator = Season::query()
            ->latest('start_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Season/SeasonsPage', [
            'seasons' => [
                'data' => SeasonResource::collection($paginator->getCollection())->resolve(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Season/CreateSeasonPage', [
            'regionOptions' => self::regionOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $season = Season::query()->create([
            ...$validated,
            'status' => 'planned',
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('season.show', $season)
            ->with('success', 'Season created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Season $season): Response
    {
        Gate::authorize('view', $season);

        $season->loadCount('harvests')
            ->loadSum('harvests', 'weight')
            ->loadSum('harvests', 'price')
            ->load([
                'harvests' => fn ($query) => $query
                    ->with('farm')
                    ->latest('harvest_date')
                    ->latest('id'),
            ]);

        return Inertia::render('Season/SeasonsProfile', [
            'season' => SeasonResource::make($season)->resolve(),
            'harvests' => HarvestResource::collection($season->harvests)->resolve(),
            'farmOptions' => self::farmOptions(),
            'pickMethodOptions' => self::pickMethodOptions()->values(),
            'harvestSeasonOptions' => self::harvestSeasonOptions(),
            'regionOptions' => self::regionOptions(),
            'statusOptions' => self::statusOptions(),
        ]);
    }

    public function createHarvest(Season $season): Response
    {
        Gate::authorize('view', $season);
        Gate::authorize('create', Harvest::class);

        $season->loadCount('harvests')
            ->load([
                'harvests' => fn ($query) => $query
                    ->with('farm.farmer')
                    ->latest('harvest_date')
                    ->latest('id'),
            ]);

        return Inertia::render('Season/NewHarvestPage', [
            'season' => SeasonResource::make($season)->resolve(),
            'harvests' => HarvestResource::collection($season->harvests)->resolve(),
            'farmOptions' => self::farmOptions(),
            'pickMethodOptions' => self::pickMethodOptions()->values(),
            'harvestSeasonOptions' => self::harvestSeasonOptions(),
        ]);
    }

    public function createBatch(Season $season): Response
    {
        Gate::authorize('view', $season);

        $season->loadCount('harvests')
            ->loadSum('harvests', 'weight')
            ->loadSum('harvests', 'price')
            ->load([
                'harvests' => fn ($query) => $query
                    ->with('farm')
                    ->latest('harvest_date')
                    ->latest('id'),
            ]);

        return Inertia::render('Batch/CreateBatch', [
            'season' => SeasonResource::make($season)->resolve(),
            'harvests' => HarvestResource::collection($season->harvests)->resolve(),
        ]);
    }

    public function storeHarvest(Request $request, Season $season): RedirectResponse
    {
        Gate::authorize('view', $season);
        Gate::authorize('create', Harvest::class);

        $pickMethodOptions = self::pickMethodOptions()->all();
        $harvestSeasonOptions = self::harvestSeasonOptions();

        $validated = $request->validate([
            'farm_id' => ['required', 'exists:farms,id'],
            'variety' => ['required', 'string', 'max:255'],
            'pick_method' => ['required', 'string', 'max:255', Rule::in($pickMethodOptions)],
            'date_planted' => ['required', 'date', 'before_or_equal:today'],
            'harvest_date' => ['required', 'date', 'before_or_equal:today'],
            'harvest_season' => ['required', 'string', 'max:255', Rule::in($harvestSeasonOptions)],
            'price' => ['required', 'numeric', 'min:0.01'],
            'weight' => ['required', 'numeric', 'min:0.01'],
            'ripeness_percentage' => ['required', 'numeric', 'between:0,100'],
            'foreign_matter_present' => ['required', 'boolean'],
            'pest_damage' => ['required', 'boolean'],
            'disease_signs' => ['required', 'boolean'],
            'visible_defects' => ['required', 'boolean'],
        ]);

        Farm::query()->with('farmer')->findOrFail($validated['farm_id']);

        $harvest = Harvest::query()->create([
            'user_id' => $request->user()->id,
            'season_id' => $season->id,
            'farm_id' => $validated['farm_id'],
            'variety' => $validated['variety'],
            'date_planted' => $validated['date_planted'],
            'harvest_date' => $validated['harvest_date'],
            'harvest_season' => $validated['harvest_season'],
            'status' => 'active',
            'pick_method' => $validated['pick_method'],
            'price' => $validated['price'],
            'weight' => $validated['weight'],
            'ripeness_percentage' => $validated['ripeness_percentage'],
            'foreign_matter_present' => $request->boolean('foreign_matter_present'),
            'pest_damage' => $request->boolean('pest_damage'),
            'disease_signs' => $request->boolean('disease_signs'),
            'visible_defects' => $request->boolean('visible_defects'),
        ]);

        return redirect()
            ->route('season.show', $season)
            ->with('success', "Harvest {$harvest->id} recorded successfully.");
    }

    public function destroyHarvest(Season $season, Harvest $harvest): RedirectResponse
    {
        Gate::authorize('view', $season);

        abort_unless((int) $harvest->season_id === (int) $season->id, 404);
        Gate::authorize('delete', $harvest);

        $harvestId = $harvest->id;
        $harvest->delete();

        return back()->with('success', "Harvest {$harvestId} deleted successfully.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Season $season): RedirectResponse
    {
        Gate::authorize('update', $season);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $season->update([
            ...$validated,
            'status' => $season->status ?: 'planned',
        ]);

        return back()->with('success', 'Season updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Season $season): RedirectResponse
    {
        Gate::authorize('delete', $season);

        $season->delete();

        return redirect()
            ->route('season.index')
            ->with('success', 'Season deleted successfully.');
    }

    protected static function statusOptions(): array
    {
        return [
            'planned',
            'active',
            'completed',
            'archived',
        ];
    }

    protected static function regionOptions(): array
    {
        return [
            'Mount Elgon',
            'Rwenzori',
            'Central Basin',
            'Northern Plateau',
            'West Nile',
        ];
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

    protected static function pickMethodOptions(): Collection
    {
        $options = PickMethodMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->values();

        if ($options->isNotEmpty()) {
            return $options;
        }

        return collect([
            'Selective Picking',
            'Strip Picking',
            'Hand Sorting',
        ]);
    }

    protected static function farmOptions(): Collection
    {
        return Farm::query()
            ->with('farmer')
            ->orderBy('name')
            ->get()
            ->map(function (Farm $farm): array {
                $farmerName = trim(implode(' ', array_filter([
                    $farm->farmer?->first_name,
                    $farm->farmer?->last_name,
                ])));

                return [
                    'id' => $farm->id,
                    'name' => $farm->name,
                    'location' => $farm->location,
                    'variety' => $farm->variety,
                    'altitude' => $farm->altitude,
                    'latitude' => $farm->latitude,
                    'longitude' => $farm->longitude,
                    'farmer_name' => $farmerName !== '' ? $farmerName : 'Unassigned farmer',
                    'region' => $farm->farmer?->district ?: $farm->location,
                ];
            })
            ->values();
    }
}
