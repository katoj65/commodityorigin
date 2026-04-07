<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Models\CropVarietyMetadata;
use App\Models\Farm;
use App\Models\Farmer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FarmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('farmer.index');
    }

    /**
     * Show the farm creation form.
     */
    public function create(Farmer $farmer): Response
    {
        return Inertia::render('Farm/Create', [
            'farmer' => [
                'id' => $farmer->id,
                'first_name' => $farmer->first_name,
                'last_name' => $farmer->last_name,
                'district' => $farmer->district,
                'sub_county' => $farmer->sub_county,
                'coffee_type' => $farmer->coffee_type,
                'cooperative' => $farmer->cooperative,
            ],
            'varietyOptions' => CropVarietyMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $varietyOptions = CropVarietyMetadata::query()
            ->where('is_active', true)
            ->pluck('name')
            ->all();

        $validated = $request->validate([
            'farmer_id' => ['required', 'exists:farmers,id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string', 'max:100'],
            'altitude' => ['nullable', 'string', 'max:100'],
            'variety' => ['required', 'string', 'max:150', Rule::in($varietyOptions)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $farm = Farm::query()->create($validated);

        return redirect()
            ->route('farmer.show', $farm->farmer_id)
            ->with('success', 'Farm added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
