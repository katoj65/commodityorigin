<?php

namespace App\Http\Controllers\Lot;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Lot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LotController extends Controller
{
    /**
     * Show the lot creation form.
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
                'size' => $farm->size,
                'altitude' => $farm->altitude,
                'variety' => $farm->variety,
                'status' => $farm->status,
                'farmer' => [
                    'id' => $farm->farmer?->id,
                    'first_name' => $farm->farmer?->first_name,
                    'last_name' => $farm->farmer?->last_name,
                    'district' => $farm->farmer?->district,
                    'sub_county' => $farm->farmer?->sub_county,
                    'telephone' => $farm->farmer?->telephone,
                ],
            ])
            ->values();

        return Inertia::render('Lot/Create', [
            'farms' => $farms,
            'processOptions' => ['Washed', 'Natural', 'Honey', 'Anaerobic'],
        ]);
    }

    /**
     * Store a newly created lot.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farm_id' => ['required', 'exists:farms,id'],
            'lot_number' => ['required', 'string', 'max:100', 'unique:lots,lot_number'],
            'process' => ['required', 'string', 'max:100'],
            'grade' => ['required', 'string', 'max:100'],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'bag_weight_kg' => ['required', 'numeric', 'min:1'],
            'reserve_price' => ['nullable', 'numeric', 'min:0'],
            'quality_score' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lot = Lot::query()->create([
            ...$validated,
            'status' => 'draft',
        ]);

        return redirect()
            ->route('farm.show', $lot->farm_id)
            ->with('success', 'Lot added successfully.');
    }
}
