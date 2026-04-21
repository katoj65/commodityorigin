<?php

namespace App\Http\Controllers\Batch;

use App\Http\Controllers\Controller;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use App\Models\BatchCompliance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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
        $batch->load('compliances');

        return Inertia::render('Batch/BatchProfile', [
            'batch' => BatchResource::make($batch)->resolve(),
        ]);
    }

    /**
     * Store a newly created batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBatchData($request);

        $batch = Batch::query()->create([
            'user_id' => $request->user()->id,
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

        $batch->update([
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
        return $request->validate([
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
        ]);
    }
}
