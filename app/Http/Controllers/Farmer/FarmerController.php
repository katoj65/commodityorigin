<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FarmerController extends Controller
{
    /**
     * Redirect farmer root traffic to the registration form.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('farmer.create');
    }

    /**
     * Show the farmer registration form.
     */
    public function create(): Response
    {
        return Inertia::render('Farmer/Create');
    }

    /**
     * Handle a farmer registration submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'sub_county' => ['nullable', 'string', 'max:255'],
            'coffee_type' => ['required', 'string', 'max:100'],
            'cooperative' => ['nullable', 'string', 'max:255'],
            'farm_size' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $farmer = Farmer::query()->create([
            ...$validated,
            'user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('farmer.show', $farmer)
            ->with('success', 'Farmer intake saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farmer $farmer): Response
    {
        return Inertia::render('Farmer/FarmerProfile', [
            'farmer' => [
                'id' => $farmer->id,
                'user_id' => $farmer->user_id,
                'first_name' => $farmer->first_name,
                'last_name' => $farmer->last_name,
                'telephone' => $farmer->telephone,
                'email' => $farmer->email,
                'district' => $farmer->district,
                'sub_county' => $farmer->sub_county,
                'coffee_type' => $farmer->coffee_type,
                'cooperative' => $farmer->cooperative,
                'farm_size' => $farmer->farm_size,
                'notes' => $farmer->notes,
                'created_at' => optional($farmer->created_at)?->toDateTimeString(),
                'updated_at' => optional($farmer->updated_at)?->toDateTimeString(),
            ],
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
