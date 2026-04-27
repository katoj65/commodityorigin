<?php

namespace App\Http\Controllers\Season;

use App\Http\Controllers\Controller;
use App\Http\Resources\HarvestResource;
use App\Http\Resources\SeasonResource;
use App\Models\Farm;
use App\Models\PickMethodMetadata;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SeasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $paginator = Season::query()
            ->latest('start_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Season/SeasonsPage', [
            'seasons' => [
                'data' => SeasonResource::collection($paginator->getCollection())->resolve(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Season/CreateSeasonPage', [
            'regionOptions' => self::regionOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $season = Season::query()->create([
            ...$validated,
            'status' => 'planned',
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('season.show', $season)
            ->with('success', 'Season created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Season $season): Response
    {
        Gate::authorize('view', $season);

        $season->loadCount('harvests')
            ->loadSum('harvests', 'weight')
            ->loadSum('harvests', 'price')
            ->load([
            'harvests' => fn ($query) => $query
                ->with('farm')
                ->latest('harvest_date')
                ->latest('id'),
            ]);

        return Inertia::render('Season/SeasonsProfile', [
            'season' => SeasonResource::make($season)->resolve(),
            'harvests' => HarvestResource::collection($season->harvests)->resolve(),
            'farmOptions' => Farm::query()
                ->with('farmer')
                ->orderBy('name')
                ->get()
                ->map(fn (Farm $farm): array => [
                    'id' => $farm->id,
                    'name' => $farm->name,
                    'location' => $farm->location,
                    'variety' => $farm->variety,
                ])
                ->values(),
            'pickMethodOptions' => PickMethodMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->values(),
            'harvestSeasonOptions' => [
                'Main Crop',
                'Fly Crop',
                'Early Harvest',
                'Late Harvest',
            ],
            'regionOptions' => self::regionOptions(),
            'statusOptions' => self::statusOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Season $season): RedirectResponse
    {
        Gate::authorize('update', $season);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $season->update([
            ...$validated,
            'status' => $season->status ?: 'planned',
        ]);

        return back()->with('success', 'Season updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Season $season): RedirectResponse
    {
        Gate::authorize('delete', $season);

        $season->delete();

        return redirect()
            ->route('season.index')
            ->with('success', 'Season deleted successfully.');
    }

    protected static function statusOptions(): array
    {
        return [
            'planned',
            'active',
            'completed',
            'archived',
        ];
    }

    protected static function regionOptions(): array
    {
        return [
            'Mount Elgon',
            'Rwenzori',
            'Central Basin',
            'Northern Plateau',
            'West Nile',
        ];
    }
}
