<?php

namespace App\Http\Controllers\Batch;

use App\Http\Controllers\Controller;
use App\Http\Resources\BatchActivityResource;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use App\Models\BatchActivity;
use App\Models\BatchActivityMetadata;
use App\Models\Currency;
use App\Services\BatchActivityService;
use App\Services\BatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BatchController extends Controller
{
    public function __construct(
        private readonly BatchService $batches,
        private readonly BatchActivityService $activities,
    ) {
    }

    /**
     * Look up a batch by its batch_number — used by the "Attach Batch"
     * modal on the lot profile page. Open to any authenticated user, same
     * as the farm/collection code lookups; the mutating action that links
     * it to a lot is what's policy-gated, not this lookup.
     */
    public function findByNumber(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'batch_number' => ['required', 'string', 'max:255'],
        ]);

        $batch = $this->batches->findByNumber($validated['batch_number']);

        if (! $batch) {
            return response()->json(['message' => 'No batch with that number was found.'], 404);
        }

        return response()->json(BatchResource::make($batch)->resolve());
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

        return Inertia::render('Batch/BatchProfile', [
            'batch' => BatchResource::make($batch)->resolve(),
            'currencyOptions' => Currency::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->pluck('code'),
            'activities' => BatchActivityResource::collection($this->activities->forBatch($batch))->resolve(),
            'activityOptions' => BatchActivityMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'name'])
                ->map(fn (BatchActivityMetadata $option): array => [
                    'slug' => $option->slug,
                    'name' => $option->name,
                ]),
        ]);
    }

    /**
     * Store a newly created batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBatchData($request);

        $batch = $this->batches->create($validated, $request->user()->id);

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
     * Link a farm collection to this batch, found by its collection_code,
     * via the batch_farm_collection pivot table.
     */
    public function attachFarmCollection(Request $request, Batch $batch): RedirectResponse
    {
        Gate::authorize('update', $batch);

        $validated = $request->validate([
            'collection_code' => ['required', 'string', 'max:255'],
        ]);

        $this->batches->attachFarmCollection($batch, $validated['collection_code'], $request->user()->id);

        return back()->with('success', 'Farm collection linked to this batch.');
    }

    /**
     * Record a manual activity-log entry for this batch — `event` must be
     * an active slug in batch_activity_metadata.
     */
    public function storeActivity(Request $request, Batch $batch): RedirectResponse
    {
        Gate::authorize('update', $batch);

        $validated = $request->validate([
            'event' => [
                'required',
                'string',
                Rule::exists('batch_activity_metadata', 'slug')->where('is_active', true),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->activities->record($batch, $validated['event'], $validated['description'] ?? null, $request->user()->id);

        return back()->with('success', 'Activity recorded.');
    }

    /**
     * Remove one activity-log entry from this batch.
     */
    public function destroyActivity(Batch $batch, BatchActivity $activity): RedirectResponse
    {
        Gate::authorize('update', $batch);
        abort_unless((int) $activity->batch_id === (int) $batch->id, 404);

        $this->activities->delete($activity);

        return back()->with('success', 'Activity removed.');
    }

    /**
     * Validate batch create/update payloads.
     *
     * @return array<string, mixed>
     */
    private function validateBatchData(Request $request, ?Batch $batch = null): array
    {
        $rules = [
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
        ];

        // batch_number is auto-generated on create and never accepted from
        // the client there; on update it's carried through read-only by the
        // edit form, so it's still validated to guard against tampering.
        if ($batch !== null) {
            $rules['batch_number'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('batches', 'batch_number')->ignore($batch),
            ];
        }

        return $request->validate($rules);
    }
}
