<?php

namespace App\Http\Controllers\FarmOwner;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmOwnerResource;
use App\Models\Farm;
use App\Models\FarmOwner;
use App\Services\FarmOwnerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FarmOwnerController extends Controller
{
    public function __construct(private readonly FarmOwnerService $owners)
    {
    }

    /**
     * List a farm's registered owners.
     */
    public function index(Farm $farm)
    {
        Gate::authorize('view', $farm);

        return FarmOwnerResource::collection($this->owners->forFarm($farm))->resolve();
    }

    /**
     * Record an owner against a farm.
     */
    public function store(Request $request, Farm $farm): RedirectResponse
    {
        Gate::authorize('update', $farm);

        $validated = $this->validated($request);

        $this->owners->store($farm, $validated, $request->user()->id);

        return back()->with('success', 'Owner recorded successfully.');
    }

    /**
     * Update an existing owner entry. The owner must belong to the farm
     * in the route, and the acting user must be able to manage that farm.
     */
    public function update(Request $request, Farm $farm, FarmOwner $owner): RedirectResponse
    {
        abort_if($owner->farm_id !== $farm->id, 404);

        Gate::authorize('update', $farm);

        $this->owners->update($owner, $this->validated($request));

        return back()->with('success', 'Owner updated successfully.');
    }

    /**
     * Remove a mistaken or duplicate owner entry.
     */
    public function destroy(Farm $farm, FarmOwner $owner): RedirectResponse
    {
        abort_if($owner->farm_id !== $farm->id, 404);

        Gate::authorize('update', $farm);

        $this->owners->delete($owner);

        return back()->with('success', 'Owner removed.');
    }

    /**
     * Validate an owner's payload — shared by store() and update().
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:100'],
            'tel' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'ownership_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
    }
}
