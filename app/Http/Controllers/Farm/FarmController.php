<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use App\Models\Farmer;
use App\Services\FarmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return Inertia::render('Farm/FamsPage', [
            'farms' => FarmResource::collection($this->farms->list())->resolve(),
        ]);
    }

    /**
     * Show the farm creation form. Accepts an optional ?farmer=ID query
     * parameter to pre-select and lock the farmer; otherwise the page
     * shows a farmer picker.
     */
    public function create(Request $request): Response
    {
        $farmer = $request->query('farmer')
            ? Farmer::query()->findOrFail($request->query('farmer'))
            : null;

        return Inertia::render('Farm/Create', [
            'farmer' => $farmer ? $this->farms->farmerSummary($farmer) : null,
            'farmerOptions' => $farmer ? [] : $this->farms->farmerOptions(),
            'varietyOptions' => $this->farms->activeVarietyOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $varietyOptions = $this->farms->activeVarietyOptions()->all();

        $validated = $request->validate([
            'farmer_id' => ['required', 'exists:farmers,id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string', 'max:100'],
            'altitude' => ['nullable', 'string', 'max:100'],
            'variety' => ['required', 'string', 'max:150', Rule::in($varietyOptions)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $farm = $this->farms->create($validated);

        return redirect()
            ->route('farmer.show', $farm->farmer_id)
            ->with('success', 'Farm added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farm $farm): Response
    {
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
