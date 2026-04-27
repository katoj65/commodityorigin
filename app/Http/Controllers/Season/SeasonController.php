<?php

namespace App\Http\Controllers\Season;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeasonResource;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search')->value());

        $paginator = Season::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('region', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
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
            'filters' => [
                'search' => $search,
            ],
            'statusOptions' => self::statusOptions(),
            'regionOptions' => self::regionOptions(),
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
            'coffee_type' => 'Not specified',
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
        return Inertia::render('Season/SeasonsProfile', [
            'season' => SeasonResource::make($season)->resolve(),
            'regionOptions' => self::regionOptions(),
            'statusOptions' => self::statusOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Season $season): RedirectResponse
    {
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
    public function destroy(string $id)
    {
        //
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
