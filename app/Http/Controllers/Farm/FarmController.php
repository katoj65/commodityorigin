<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClimateZoneMetadataResource;
use App\Http\Resources\FarmCollectionResource;
use App\Http\Resources\FarmDocumentResource;
use App\Http\Resources\FarmResource;
use App\Http\Resources\SoilMetadataResource;
use App\Http\Resources\WeatherForecastResource;
use App\Models\Farm;
use App\Models\FarmCollection;
use App\Models\FarmDocument;
use App\Models\Harvest;
use App\Services\ClimateZoneMetadataService;
use App\Services\FarmCollectionService;
use App\Services\FarmDocumentService;
use App\Services\FarmService;
use App\Services\HarvestService;
use App\Services\SoilMetadataService;
use App\Services\WeatherForecastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FarmController extends Controller
{
    public function __construct(
        private readonly FarmService $farms,
        private readonly HarvestService $harvests,
        private readonly WeatherForecastService $weather,
        private readonly FarmDocumentService $documents,
        private readonly SoilMetadataService $soils,
        private readonly ClimateZoneMetadataService $climateZones,
        private readonly FarmCollectionService $collections,
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
     * Look up a farm strictly by its farm code — no ownership scoping —
     * used by the "New Farm Collection" modal's farm-code text field to
     * resolve a farm id before submitting. A match here doesn't mean the
     * caller may act on the farm; FarmPolicy::update() still enforces
     * that separately when the resolved id is actually used (e.g. on
     * farm.collections.store).
     */
    public function findByCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'farm_code' => ['required', 'string', 'max:255'],
        ]);

        $farm = $this->farms->findByCode($validated['farm_code']);

        if (! $farm) {
            return response()->json(['message' => 'No farm with that code was found.'], 404);
        }

        return response()->json([
            'id' => $farm->id,
            'name' => $farm->name,
            'farm_code' => $farm->farm_code,
        ]);
    }

    /**
     * Store a newly created resource in storage. Always submitted for a
     * specific, already-known farmer (see AddFarmDialog.vue on the
     * farmer profile page) — no self-farmer or inline-registration
     * branching needed here.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Farm::class);

        $validated = $request->validate([
            'farmer_id' => ['required', 'integer', 'exists:farmers,id'],
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'subcounty' => ['nullable', 'string', 'max:255'],
            'parish' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'elevation' => ['nullable', 'numeric'],
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'coffee_area' => ['nullable', 'numeric', 'min:0'],
            'coffee_type' => ['nullable', 'string', 'max:100'],
            'tel' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'soil_metadata_id' => ['nullable', 'integer', 'exists:soil_metadata,id'],
            'climate_zone_metadata_id' => ['nullable', 'integer', 'exists:climate_zone_metadata,id'],
            'water_conservation_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'carbon_sequestration' => ['nullable', 'numeric', 'min:0'],
            'soil_health_index' => ['nullable', 'numeric', 'between:0,5'],
            'soil_type' => ['nullable', 'string', 'max:150'],
            'crop_variety_ids' => ['nullable', 'array'],
            'crop_variety_ids.*' => ['integer', 'exists:crop_variety_metadata,id'],
            'certification_ids' => ['nullable', 'array'],
            'certification_ids.*' => ['integer', 'exists:certification_metadata,id'],
        ]);

        $this->farms->create([
            ...$validated,
            'created_by_user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Farm added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farm $farm): Response
    {
        Gate::authorize('view', $farm);

        $farm = $this->farms->show($farm);

        $weatherRegion = $this->weather->matchRegionFor($farm->district);
        $weatherOutlook = $weatherRegion ? $this->weather->monthlyOutlookForRegion($weatherRegion) : collect();

        return Inertia::render('Farm/FarmProfile', [
            'farm' => FarmResource::make($farm)->resolve(),
            'canEdit' => Gate::allows('update', $farm),
            'varietyOptions' => $this->farms->activeVarietyOptions(),
            'cropVarietyOptions' => $this->farms->activeVarietyMetadata(),
            'certificationOptions' => $this->farms->activeCertificationOptions(),
            'soilTypeOptions' => SoilMetadataResource::collection($this->soils->active())->resolve(),
            'climaticZoneOptions' => ClimateZoneMetadataResource::collection($this->climateZones->active())->resolve(),
            'harvestSeasonOptions' => $this->harvests->harvestSeasonOptions(),
            'weatherRegion' => $weatherRegion,
            'weatherOutlook' => WeatherForecastResource::collection($weatherOutlook)->resolve(),
            'documents' => FarmDocumentResource::collection($farm->documents)->resolve(),
            'documentTypeOptions' => $this->harvests->documentTypeOptions(),
            'collections' => FarmCollectionResource::collection($farm->collections)->resolve(),
            'collectionUnitOptions' => $this->collections->unitOptions(),
            'collectionPaymentStatusOptions' => $this->collections->paymentStatusOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage. Creator only. Covers every
     * editable farm field (identity, address, geolocation, size) in one
     * form — see AddFarmDialog.vue / the Edit Farm dialog on FarmProfile.
     */
    public function update(Request $request, Farm $farm): RedirectResponse
    {
        Gate::authorize('update', $farm);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'subcounty' => ['nullable', 'string', 'max:255'],
            'parish' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'elevation' => ['nullable', 'numeric'],
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'coffee_area' => ['nullable', 'numeric', 'min:0'],
            'coffee_type' => ['nullable', 'string', 'max:100'],
            'tel' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'soil_metadata_id' => ['nullable', 'integer', 'exists:soil_metadata,id'],
            'climate_zone_metadata_id' => ['nullable', 'integer', 'exists:climate_zone_metadata,id'],
            'water_conservation_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'carbon_sequestration' => ['nullable', 'numeric', 'min:0'],
            'soil_health_index' => ['nullable', 'numeric', 'between:0,5'],
            'soil_type' => ['nullable', 'string', 'max:150'],
            'crop_variety_ids' => ['nullable', 'array'],
            'crop_variety_ids.*' => ['integer', 'exists:crop_variety_metadata,id'],
            'certification_ids' => ['nullable', 'array'],
            'certification_ids.*' => ['integer', 'exists:certification_metadata,id'],
        ]);

        $this->farms->update($farm, $validated);

        return back()->with('success', 'Farm updated successfully.');
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
     * Record a new coffee collection against this farm. A collection is
     * its own piece of content — any authenticated user (route middleware
     * already restricts to farmer/admin roles) may record one against any
     * farm found by code; it isn't gated by who created the farm itself.
     */
    public function storeCollection(Request $request, Farm $farm): RedirectResponse
    {
        $validated = $request->validate($this->collectionValidationRules());

        $collection = $this->collections->create([
            ...$validated,
            'farm_id' => $farm->id,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('farm-collection.show', $collection)
            ->with('success', 'Collection recorded successfully.');
    }

    /**
     * Update an existing coffee collection. Only the user who recorded it
     * (or an admin) may edit it — ownership of the collection itself, not
     * of the farm it was recorded against.
     */
    public function updateCollection(Request $request, Farm $farm, FarmCollection $collection): RedirectResponse
    {
        abort_unless($collection->farm_id === $farm->id, 404);
        abort_unless($request->user()->isAdmin() || $collection->user_id === $request->user()->id, 403);

        $validated = $request->validate($this->collectionValidationRules());

        $this->collections->update($collection, $validated);

        return back()->with('success', 'Collection updated successfully.');
    }

    /**
     * Delete a coffee collection. Only the user who recorded it (or an
     * admin) may delete it.
     */
    public function destroyCollection(Request $request, Farm $farm, FarmCollection $collection): RedirectResponse
    {
        abort_unless($collection->farm_id === $farm->id, 404);
        abort_unless($request->user()->isAdmin() || $collection->user_id === $request->user()->id, 403);

        $this->collections->delete($collection);

        return back()->with('success', 'Collection deleted successfully.');
    }

    /**
     * Validation rules shared by storeCollection() and updateCollection().
     *
     * @return array<string, array<int, mixed>>
     */
    private function collectionValidationRules(): array
    {
        $unitOptions = $this->collections->unitOptions();
        $paymentStatusOptions = $this->collections->paymentStatusOptions();

        return [
            'collection_date' => ['required', 'date', 'before_or_equal:today'],
            'coffee_type' => ['required', 'string', 'max:100'],
            'variety' => ['nullable', 'string', 'max:255'],
            'harvest_season' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:20', Rule::in($unitOptions)],
            'initial_moisture' => ['nullable', 'numeric', 'between:0,100'],
            'initial_defects' => ['nullable', 'numeric', 'min:0'],
            'initial_grade' => ['nullable', 'string', 'max:100'],
            'initial_quality_score' => ['nullable', 'numeric', 'between:0,100'],
            'collection_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_status' => ['nullable', 'string', 'max:50', Rule::in($paymentStatusOptions)],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Upload a document against this farm. Creator only.
     */
    public function storeDocument(Request $request, Farm $farm): RedirectResponse
    {
        Gate::authorize('update', $farm);

        $documentTypeOptions = $this->harvests->documentTypeOptions()->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', 'max:255', Rule::in($documentTypeOptions)],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx', 'max:10240'],
        ]);

        $this->documents->store($farm, $request->file('document'), $validated, $request->user()->id);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Delete a document uploaded against this farm. Creator only.
     */
    public function destroyDocument(Farm $farm, FarmDocument $document): RedirectResponse
    {
        Gate::authorize('update', $farm);

        abort_unless($document->farm_id === $farm->id, 404);

        $this->documents->delete($document);

        return back()->with('success', 'Document deleted successfully.');
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



