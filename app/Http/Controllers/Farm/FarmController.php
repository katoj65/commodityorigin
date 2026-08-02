<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClimateZoneMetadataResource;
use App\Http\Resources\FarmResource;
use App\Http\Resources\HarvestResource;
use App\Http\Resources\SoilMetadataResource;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\Harvest;
use App\Services\ClimateZoneMetadataService;
use App\Services\FarmService;
use App\Services\HarvestService;
use App\Services\SoilMetadataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FarmController extends Controller
{
    public function __construct(
        private readonly FarmService $farms,
        private readonly ClimateZoneMetadataService $climateZones,
        private readonly SoilMetadataService $soils,
        private readonly HarvestService $harvests,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Farm::class);

        return Inertia::render('Farm/FamsPage', [
            'farms' => FarmResource::collection($this->farms->list())->resolve(),
        ]);
    }

    /**
     * Display the farms created by the currently authenticated user.
     */
    public function myFarms(Request $request): Response
    {
        return Inertia::render('Farm/MyFarms', [
            'farms' => FarmResource::collection($this->farms->listForUser($request->user()->id))->resolve(),
            'varietyOptions' => $this->farms->activeVarietyOptions(),
        ]);
    }

    /**
     * Show the farm creation form. Accepts an optional ?farmer=ID query
     * parameter to pre-select and lock the farmer; otherwise the page
     * lets the user say whether they are the farmer.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Farm::class);

        $farmer = $request->query('farmer')
            ? Farmer::query()->findOrFail($request->query('farmer'))
            : null;

        return Inertia::render('Farm/Create', [
            'farmer' => $farmer ? $this->farms->farmerSummary($farmer) : null,
            'varietyOptions' => $this->farms->activeVarietyOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage. The farmer attached to
     * the farm is resolved one of three ways: an explicit farmer_id (the
     * ?farmer= locked flow), the authenticated user's own farmer record
     * (is_self_farmer), or a freshly registered farmer (farmer.*).
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Farm::class);

        $varietyOptions = $this->farms->activeVarietyOptions()->all();

        $farmData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string', 'max:100'],
            'altitude' => ['nullable', 'string', 'max:100'],
            'variety' => ['required', 'string', 'max:150', Rule::in($varietyOptions)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $farm = DB::transaction(function () use ($request, $farmData) {
            if ($request->filled('farmer_id')) {
                $request->validate(['farmer_id' => ['exists:farmers,id']]);
                $farmer = Farmer::query()->findOrFail($request->input('farmer_id'));
            } elseif ($request->boolean('is_self_farmer')) {
                $farmer = $this->farms->farmerForUser($request->user());
            } else {
                $farmerData = $request->validate([
                    'farmer.first_name' => ['required', 'string', 'max:255'],
                    'farmer.last_name' => ['required', 'string', 'max:255'],
                    'farmer.telephone' => ['required', 'string', 'max:50'],
                    'farmer.email' => ['nullable', 'email', 'max:255'],
                    'farmer.district' => ['required', 'string', 'max:255'],
                    'farmer.sub_county' => ['nullable', 'string', 'max:255'],
                    'farmer.coffee_type' => ['required', 'string', 'max:100'],
                    'farmer.cooperative' => ['nullable', 'string', 'max:255'],
                ])['farmer'];
                $farmer = $this->farms->registerFarmer($farmerData);
            }

            return $this->farms->create([
                ...$farmData,
                'farmer_id' => $farmer->id,
                'created_by_user_id' => $request->user()->id,
            ]);
        });

        // Whoever registers a farm can always view its profile page, even
        // without directory access (see FarmPolicy::view()).
        return redirect()
            ->route('farm.show', $farm->id)
            ->with('success', 'Farm added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farm $farm): Response
    {
        Gate::authorize('view', $farm);

        $farm = $this->farms->show($farm);

        return Inertia::render('Farm/FarmProfile', [
            'farm' => FarmResource::make($farm)->resolve(),
            'canEdit' => Gate::allows('update', $farm),
            'varietyOptions' => $this->farms->activeVarietyOptions(),
            'climaticZoneOptions' => ClimateZoneMetadataResource::collection($this->climateZones->active())->resolve(),
            'soilTypeOptions' => SoilMetadataResource::collection($this->soils->active())->resolve(),
            'harvests' => HarvestResource::collection($farm->harvests)->resolve(),
            'pickMethodOptions' => $this->harvests->pickMethodOptions(),
            'harvestSeasonOptions' => $this->harvests->harvestSeasonOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage. Creator only.
     */
    public function update(Request $request, Farm $farm): RedirectResponse
    {
        Gate::authorize('update', $farm);

        $varietyOptions = $this->farms->activeVarietyOptions()->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string', 'max:100'],
            'altitude' => ['nullable', 'string', 'max:100'],
            'variety' => ['required', 'string', 'max:150', Rule::in($varietyOptions)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->farms->update($farm, $validated);

        return back()->with('success', 'Farm updated successfully.');
    }

    /**
     * Update the farm's environmental specifications. Creator only.
     */
    public function updateSpecs(Request $request, Farm $farm): RedirectResponse
    {
        Gate::authorize('update', $farm);

        $climaticZoneOptions = $this->climateZones->activeNames()->all();
        $soilTypeOptions = $this->soils->activeNames()->all();

        $validated = $request->validate([
            'rainfall' => ['nullable', 'string', 'max:100'],
            'temperature' => ['nullable', 'string', 'max:100'],
            'humidity' => ['nullable', 'string', 'max:100'],
            'soil_type' => ['nullable', 'string', 'max:150', Rule::in($soilTypeOptions)],
            'climatic_zone' => ['nullable', 'string', 'max:150', Rule::in($climaticZoneOptions)],
        ]);

        $this->farms->update($farm, $validated);

        return back()->with('success', 'Environmental specifications updated successfully.');
    }

    /**
     * Update the farm's location coordinates and altitude. Creator only.
     */
    public function updateLocation(Request $request, Farm $farm): RedirectResponse
    {
        Gate::authorize('update', $farm);

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'altitude' => ['nullable', 'string', 'max:100'],
        ]);

        $this->farms->update($farm, $validated);

        return back()->with('success', 'Farm location updated successfully.');
    }

    /**
     * Record a new harvest for this farm. Creator only.
     */
    public function storeHarvest(Request $request, Farm $farm): RedirectResponse
    {
        Gate::authorize('update', $farm);

        $pickMethodOptions = $this->harvests->pickMethodOptions()->all();
        $harvestSeasonOptions = $this->harvests->harvestSeasonOptions();
        $varietyOptions = $this->farms->activeVarietyOptions()->all();

        $validated = $request->validate(
            [
                'variety' => ['required', 'string', 'max:255', Rule::in($varietyOptions)],
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

        $this->harvests->create([
            ...$validated,
            'farm_id' => $farm->id,
            'user_id' => $request->user()->id,
            'foreign_matter_present' => $request->boolean('foreign_matter_present'),
            'pest_damage' => $request->boolean('pest_damage'),
            'disease_signs' => $request->boolean('disease_signs'),
            'visible_defects' => $request->boolean('visible_defects'),
        ]);

        return back()->with('success', 'Harvest recorded successfully.');
    }

    /**
     * Delete a harvest recorded against this farm. Creator only.
     */
    public function destroyHarvest(Farm $farm, Harvest $harvest): RedirectResponse
    {
        Gate::authorize('update', $farm);

        abort_unless($harvest->farm_id === $farm->id, 404);

        $this->harvests->delete($harvest);

        return back()->with('success', 'Harvest deleted successfully.');
    }

    /**
     * Remove the specified resource from storage. Creator only.
     */
    public function destroy(Farm $farm): RedirectResponse
    {
        Gate::authorize('delete', $farm);

        $this->farms->destroy($farm);

        return redirect()->route('dashboard')->with('success', 'Farm deleted successfully.');
    }
}



