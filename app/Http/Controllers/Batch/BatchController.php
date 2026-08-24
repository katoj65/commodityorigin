<?php

namespace App\Http\Controllers\Batch;

use App\Http\Controllers\Controller;
use App\Http\Resources\BatchResource;
use App\Http\Resources\HarvestResource;
use App\Http\Resources\SeasonResource;
use App\Models\Batch;
use App\Models\Currency;
use App\Services\BatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BatchController extends Controller
{
    public function __construct(private readonly BatchService $batches)
    {
    }

    /**
     * Display the batch directory.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search')->value());

        $paginator = $this->batches
            ->paginate($search)
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
            'stats' => $this->batches->stats(),
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
        $this->batches->loadProfileRelations($batch);

        $seasonId = $this->batches->resolveSeasonId($batch);

        $season = $seasonId
            ? $this->batches->findSeasonWithHarvestStats($seasonId)
            : null;

        $harvests = $this->batches->harvestsForSeason($seasonId);

        return Inertia::render('Batch/BatchProfile', [
            'batch' => BatchResource::make($batch)->resolve(),
            'season' => $season ? SeasonResource::make($season)->resolve() : null,
            'harvests' => HarvestResource::collection($harvests)->resolve(),
            'currencyOptions' => Currency::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->pluck('code'),
        ]);
    }

    /**
     * Store a newly created batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBatchData($request);

        $batch = $this->batches->create($validated, $request->user()->id);

        $harvestIds = collect($validated['harvest_ids'] ?? [])->map(fn ($id): int => (int) $id)->all();

        $this->batches->attachHarvests($batch, $harvestIds, $request->user()->id);

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

        $this->batches->update($batch, $validated);

        return back()->with('success', 'Batch updated successfully.');
    }

    /**
     * Delete the specified batch.
     */
    public function destroy(Batch $batch): RedirectResponse
    {
        Gate::authorize('delete', $batch);

        $this->batches->destroy($batch);

        return redirect()
            ->route('batch.index')
            ->with('success', 'Batch deleted successfully.');
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

        $this->batches->createCompliance($batch, $request->user()->id, $validated);

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
                $batch === null ? 'nullable' : 'required',
                'string',
                'max:100',
                Rule::unique('batches', 'batch_number')->ignore($batch),
            ],
            'variety' => ['required', 'string', 'max:255'],
            'warehouse_location' => ['required', 'string', 'max:255'],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'net_weight_kg' => ['required', 'numeric', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
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
            'harvest_ids' => ['nullable', 'array'],
            'harvest_ids.*' => ['integer', 'exists:harvests,id'],
        ]);

        return $validated;
    }
}
