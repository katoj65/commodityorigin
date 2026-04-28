<?php

namespace App\Http\Controllers\Batch;

use App\Http\Controllers\Controller;
use App\Http\Resources\BatchResource;
use App\Http\Resources\HarvestResource;
use App\Http\Resources\SeasonResource;
use App\Models\Batch;
use App\Models\BatchCompliance;
use App\Models\BatchOwnership;
use App\Models\Harvest;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BatchController extends Controller
{
    /**
     * Display the batch directory.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search')->value());

        $paginator = Batch::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query
                    ->where('batch_number', 'like', "%{$search}%")
                    ->orWhere('warehouse_location', 'like', "%{$search}%");
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Batch $batch): array => [
                ...BatchResource::make($batch)->resolve(),
                'show_url' => route('batch.show', $batch),
            ]);

        return Inertia::render('Batch/BatchesPage', [
            'batches' => [
                'data' => array_values($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'stats' => [
                'total_batches' => Batch::query()->count(),
                'received_batches' => Batch::query()->where('status', 'received')->count(),
                'total_weight' => (float) Batch::query()->sum('weight'),
            ],
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the batch creation form.
     */
    public function create(): Response
    {
        return Inertia::render('Batch/Create');
    }

    /**
     * Display the specified batch profile.
     */
    public function show(Batch $batch): Response
    {
        Gate::authorize('view', $batch);
        $batch->load(['compliances', 'ownerships', 'season']);

        $seasonId = $batch->season_id ?: $batch->ownerships
            ->firstWhere('owner_type', Season::class)
            ?->owner_id;

        /** @var Collection<int, int> $harvestIds */
        $harvestIds = $batch->ownerships
            ->where('owner_type', Harvest::class)
            ->pluck('owner_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->values();

        $season = $seasonId
            ? Season::query()
                ->withCount('harvests')
                ->withSum('harvests', 'weight')
                ->withSum('harvests', 'price')
                ->find($seasonId)
            : null;

        $harvests = Harvest::query()
            ->where("season_id",$seasonId)
            ->with('farm')
            ->get();

        return Inertia::render('Batch/BatchProfile', [
            'batch' => BatchResource::make($batch)->resolve(),
            'season' => $season ? SeasonResource::make($season)->resolve() : null,
            'harvests' => HarvestResource::collection($harvests)->resolve(),
        ]);
    }

    /**
     * Store a newly created batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBatchData($request);
        $seasonId = $validated['season_id'] ?? null;

        if ($seasonId) {
            $season = Season::query()->findOrFail($seasonId);
            Gate::authorize('view', $season);
        }

        $batch = Batch::query()->create([
            'user_id' => $request->user()->id,
            'season_id' => $seasonId,
            'batch_number' => $validated['batch_number'],
            'variety' => $validated['variety'] ?? null,
            'warehouse_location' => $validated['warehouse_location'],
            'quantity' => $validated['quantity_bags'],
            'weight' => $validated['net_weight_kg'],
            'price' => $validated['price'] ?? null,
            'moisture_content' => $validated['moisture_content'] ?? null,
            'processing_date' => $validated['processing_date'] ?? null,
            'processing_method' => $validated['processing_method'] ?? null,
            'drying_method' => $validated['drying_method'] ?? null,
            'drying_duration' => $validated['drying_duration'] ?? null,
            'milling_status' => $validated['milling_status'] ?? null,
            'screen_size' => $validated['screen_size'] ?? null,
            'defect_count' => $validated['defect_count'] ?? null,
            'cup_score' => $validated['cup_score'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'received',
        ]);

        $harvestIds = collect($validated['harvest_ids'] ?? [])->map(fn ($id): int => (int) $id);

        if ($harvestIds->isNotEmpty()) {
            Harvest::query()
                ->whereKey($harvestIds)
                ->get()
                ->each(function (Harvest $harvest) use ($batch, $request): void {
                    BatchOwnership::query()->create([
                        'batch_id' => $batch->id,
                        'user_id' => $request->user()->id,
                        'owner_id' => $harvest->id,
                        'owner_type' => Harvest::class,
                    ]);
                });
        }

        return redirect()
            ->route('batch.show', $batch)
            ->with('success', 'Batch added successfully.');
    }

    /**
     * Update the specified batch.
     */
    public function update(Request $request, Batch $batch): RedirectResponse
    {
        Gate::authorize('update', $batch);

        $validated = $this->validateBatchData($request, $batch);
        $seasonId = $validated['season_id'] ?? $batch->season_id;

        if ($seasonId) {
            $season = Season::query()->findOrFail($seasonId);
            Gate::authorize('view', $season);
        }

        $batch->update([
            'season_id' => $seasonId,
            'batch_number' => $validated['batch_number'],
            'variety' => $validated['variety'],
            'warehouse_location' => $validated['warehouse_location'],
            'quantity' => $validated['quantity_bags'],
            'weight' => $validated['net_weight_kg'],
            'price' => $validated['price'],
            'moisture_content' => $validated['moisture_content'] ?? null,
            'processing_date' => $validated['processing_date'],
            'processing_method' => $validated['processing_method'],
            'drying_method' => $validated['drying_method'],
            'drying_duration' => $validated['drying_duration'] ?? null,
            'milling_status' => $validated['milling_status'] ?? null,
            'screen_size' => $validated['screen_size'] ?? null,
            'defect_count' => $validated['defect_count'] ?? null,
            'cup_score' => $validated['cup_score'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Batch updated successfully.');
    }

    /**
     * Store compliance metadata for the specified batch.
     */
    public function storeCompliance(Request $request, Batch $batch): RedirectResponse
    {
        Gate::authorize('update', $batch);

        $validated = $request->validate([
            'compliance_type' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected', 'expired'])],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'issued_by' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        BatchCompliance::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return back()->with('success', 'Batch compliance saved successfully.');
    }

    /**
     * Validate batch create/update payloads.
     *
     * @return array<string, mixed>
     */
    private function validateBatchData(Request $request, ?Batch $batch = null): array
    {
        $validated = $request->validate([
            'batch_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('batches', 'batch_number')->ignore($batch),
            ],
            'variety' => ['required', 'string', 'max:255'],
            'warehouse_location' => ['required', 'string', 'max:255'],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'net_weight_kg' => ['required', 'numeric', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'moisture_content' => ['nullable', 'numeric', 'between:0,100'],
            'processing_date' => ['required', 'date', 'before_or_equal:today'],
            'processing_method' => ['required', 'string', 'max:255'],
            'drying_method' => ['required', 'string', 'max:255'],
            'drying_duration' => ['nullable', 'integer', 'min:0'],
            'milling_status' => ['nullable', 'string', 'max:255'],
            'screen_size' => ['nullable', 'string', 'max:255'],
            'defect_count' => ['nullable', 'integer', 'min:0'],
            'cup_score' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'season_id' => ['nullable', 'exists:seasons,id'],
            'harvest_ids' => ['nullable', 'array'],
            'harvest_ids.*' => ['integer', 'exists:harvests,id'],
        ]);

        if (!empty($validated['season_id']) && !empty($validated['harvest_ids'])) {
            $mismatchedHarvest = Harvest::query()
                ->whereKey($validated['harvest_ids'])
                ->where('season_id', '!=', $validated['season_id'])
                ->exists();

            if ($mismatchedHarvest) {
                throw ValidationException::withMessages([
                    'harvest_ids' => 'Selected harvests must belong to the chosen season.',
                ]);
            }
        }

        return $validated;
    }
}
