<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmerResource;
use App\Models\Cooperative;
use App\Models\Farmer;
use App\Services\FarmerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FarmerController extends Controller
{
    public function __construct(private readonly FarmerService $farmers)
    {
    }

    /**
     * Display the farmer directory.
     */
    public function index(): Response
    {
        return Inertia::render('Farmer/FarmersPage', [
            'farmers' => FarmerResource::collection($this->farmers->all())->resolve(),
        ]);
    }

    /**
     * Show the farmer registration form.
     */
    public function create(): Response
    {
        return Inertia::render('Farmer/Create', [
            'cooperatives' => Cooperative::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Handle a farmer registration submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $farmer = $this->farmers->create([
            ...$validated,
            'user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('farmer.show', $farmer)
            ->with('success', 'Farmer registered successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farmer $farmer): Response
    {
        $farmer->load(['farms', 'cooperative']);

        return Inertia::render('Farmer/FarmerProfile', [
            'farmer' => FarmerResource::make($farmer)->resolve(),
            'cooperatives' => Cooperative::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Farmer $farmer): RedirectResponse
    {
        $validated = $this->validated($request, $farmer);

        $this->farmers->update($farmer, $validated);

        return redirect()
            ->route('farmer.show', $farmer)
            ->with('success', 'Farmer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Farmer $farmer): RedirectResponse
    {
        $this->farmers->delete($farmer);

        return redirect()
            ->route('farmer.index')
            ->with('success', 'Farmer removed successfully.');
    }

    /**
     * Shared validation rules for store/update. `farmer_number` and
     * `national_id` must be unique, ignoring the current record on update.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Farmer $farmer = null): array
    {
        return $request->validate([
            'farmer_number' => ['nullable', 'string', 'max:50', 'unique:farmers,farmer_number,'.($farmer?->id ?? 'NULL')],
            'cooperative_id' => ['nullable', 'integer', 'exists:cooperatives,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'tel' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'subcounty' => ['nullable', 'string', 'max:255'],
            'parish' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50', 'unique:farmers,national_id,'.($farmer?->id ?? 'NULL')],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'verification_status' => ['nullable', 'string', 'in:pending,verified,rejected'],
        ]);
    }
}
