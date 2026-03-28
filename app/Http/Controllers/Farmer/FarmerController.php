<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
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
        $request->validate([
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

        return redirect()
            ->route('farmer.create')
            ->with('success', 'Farmer intake captured. Persistence can be connected next.');
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
