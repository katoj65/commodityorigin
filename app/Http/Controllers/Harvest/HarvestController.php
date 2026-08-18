<?php

namespace App\Http\Controllers\Harvest;

use App\Http\Controllers\Controller;
use App\Http\Resources\HarvestResource;
use App\Http\Resources\SeasonResource;
use App\Models\Harvest;
use App\Models\Season;
use App\Services\FarmService;
use App\Services\HarvestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HarvestController extends Controller
{
    public function __construct(
        private readonly HarvestService $harvests,
        private readonly FarmService $farms,
    ) {
    }

    /**
     * Display the harvest directory.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search')->value());

        $paginator = $this->harvests->paginateForList($search);

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
            'stats' => $this->harvests->indexStats(),
            'filters' => [
                'search' => $search,
            ],
            'estateOptions' => $this->harvests->estateOptions(),
        ]);
    }

    /**
     * Display the harvests recorded by the current user, across every
     * farm they created. Admins see every harvest, from every creator.
     */
    public function mine(Request $request): Response
    {
        Gate::authorize('viewAny', Harvest::class);

        $user = $request->user();

        $harvests = $user->isAdmin()
            ? $this->harvests->listAll()
            : $this->harvests->listForUser($user->id);

        return Inertia::render('Farm/MyHarvests', [
            'harvests' => HarvestResource::collection($harvests)->resolve(),
            'pickMethodOptions' => $this->harvests->pickMethodOptions(),
            'harvestSeasonOptions' => $this->harvests->harvestSeasonOptions(),
            'isAdmin' => $user->isAdmin(),
            // Only the farms this user created — adding a harvest is gated
            // per-farm by FarmPolicy::update (creator only), so an admin
            // viewing everyone's harvests here can still only attach a new
            // one to a farm they themselves created.
            'farmOptions' => $this->farms->listForUser($user->id)->map(fn ($farm) => [
                'id' => $farm->id,
                'name' => $farm->name,
                'location' => $farm->location,
            ])->values(),
            'varietyOptions' => $this->farms->activeVarietyOptions(),
        ]);
    }

    /**
     * Show the harvest creation form.
     */
    public function create(): Response
    {
        Gate::authorize('create', Harvest::class);

        return Inertia::render('Harvest/Create', [
            'farmOptions' => $this->harvests->farmOptionsForCreate(),
            'pickMethodOptions' => $this->harvests->pickMethodOptions(),
            'harvestSeasonOptions' => $this->harvests->harvestSeasonOptions(),
        ]);
    }

    /**
     * Display the specified harvest profile.
     */
    public function show(Harvest $harvest): Response
    {
        $harvest->load([
            'farm.farmer',
            'season',
            'creator',
            'sustainability',
            'documents' => fn ($query) => $query->latest(),
        ]);
        Gate::authorize('view', $harvest);

        $seasonPayload = null;

        if ($harvest->season) {
            $harvest->season
                ->loadCount('harvests')
                ->loadSum('harvests', 'weight')
                ->loadSum('harvests', 'price');

            $seasonPayload = SeasonResource::make($harvest->season)->resolve();
        }

        return Inertia::render('Harvest/HarvestProfile', [
            'harvest' => HarvestResource::make($harvest)->resolve(),
            'season' => $seasonPayload,
            'dateRange' => $this->harvests->getRangeOfDates(
                $harvest->date_planted?->toDateString() ?? '',
                $harvest->harvest_date?->toDateString() ?? '',
            ),
            'pickMethodOptions' => $this->harvests->pickMethodOptions(),
            'harvestSeasonOptions' => $this->harvests->harvestSeasonOptions(),
            'documentTypeOptions' => $this->harvests->documentTypeOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Harvest::class);

        $pickMethodOptions = $this->harvests->pickMethodOptions()->all();
        $harvestSeasonOptions = $this->harvests->harvestSeasonOptions();

        $validated = $request->validate(
            [
                'season_id' => ['nullable', 'exists:seasons,id'],
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

        if (! empty($validated['season_id'])) {
            $season = Season::query()->findOrFail($validated['season_id']);
            Gate::authorize('view', $season);
        }

        $harvest = $this->harvests->create([
            ...$validated,
            'user_id' => $request->user()->id,
            'foreign_matter_present' => $request->boolean('foreign_matter_present'),
            'pest_damage' => $request->boolean('pest_damage'),
            'disease_signs' => $request->boolean('disease_signs'),
            'visible_defects' => $request->boolean('visible_defects'),
        ]);

        if ($harvest->season_id) {
            return redirect()
                ->route('season.show', $harvest->season_id)
                ->with('success', "Harvest {$harvest->id} recorded successfully.");
        }

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

        $pickMethodOptions = $this->harvests->pickMethodOptions()->all();
        $harvestSeasonOptions = $this->harvests->harvestSeasonOptions();

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

        $this->harvests->update($harvest, [
            ...$validated,
            'foreign_matter_present' => $request->boolean('foreign_matter_present'),
            'pest_damage' => $request->boolean('pest_damage'),
            'disease_signs' => $request->boolean('disease_signs'),
            'visible_defects' => $request->boolean('visible_defects'),
        ]);

        return back();
    }

    /**
     * Delete the specified harvest record. Creator or admin only.
     */
    public function destroy(Harvest $harvest): RedirectResponse
    {
        Gate::authorize('delete', $harvest);

        $this->harvests->delete($harvest);

        return back()->with('success', 'Harvest deleted successfully.');
    }

    /**
     * Store a document for the specified harvest.
     */
    public function storeDocument(Request $request, Harvest $harvest): RedirectResponse
    {
        Gate::authorize('update', $harvest);

        $documentTypeOptions = $this->harvests->documentTypeOptions()->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:255', Rule::in($documentTypeOptions)],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        $this->harvests->storeDocument($harvest, $request->file('document'), $validated, $request->user()->id);

        return back();
    }

    /**
     * Store sustainability details for the specified harvest.
     */
    public function storeHarvestSustainability(Request $request, Harvest $harvest): RedirectResponse
    {
        Gate::authorize('update', $harvest);

        $validated = $request->validate([
            'organicCertified' => ['required', 'boolean'],
            'climateSmart' => ['required', 'boolean'],
            'shadeGrown' => ['required', 'boolean'],
            'waterManagement' => ['required', 'boolean'],
            'soilConservation' => ['required', 'boolean'],
            'lowCarbon' => ['required', 'boolean'],
            'fairWages' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->harvests->storeSustainability($harvest, $validated, $request->user()->id);

        return back()->with('success', 'Harvest sustainability details saved successfully.');
    }
}
