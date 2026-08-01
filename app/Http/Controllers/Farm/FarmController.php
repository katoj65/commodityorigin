<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use App\Models\Farmer;
use App\Services\FarmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FarmController extends Controller
{
    public function __construct(private readonly FarmService $farms)
    {
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

            return $this->farms->create([...$farmData, 'farmer_id' => $farmer->id]);
        });

        return redirect()
            ->route('farmer.show', $farm->farmer_id)
            ->with('success', 'Farm added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farm $farm): Response
    {
        Gate::authorize('view', $farm);

        return Inertia::render('Farm/FarmProfile', [
            'farm' => FarmResource::make($this->farms->show($farm))->resolve(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
