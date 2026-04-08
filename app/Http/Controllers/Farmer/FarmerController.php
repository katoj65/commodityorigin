<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmerResource;
use App\Models\Farmer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FarmerController extends Controller
{
    /**
     * Display the farmer directory.
     */
    public function index(): Response
    {
        $farmers = Farmer::query()
            ->withCount('farms')
            ->latest()
            ->get();

        return Inertia::render('Farmer/FarmersPage', [
            'farmers' => FarmerResource::collection($farmers)->resolve(),
        ]);
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
        $farmer->load('farms');

        return Inertia::render('Farmer/FarmerProfile', [
            'farmer' => FarmerResource::make($farmer)->resolve(),
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
