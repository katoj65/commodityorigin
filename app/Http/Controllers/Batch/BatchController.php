<?php

namespace App\Http\Controllers\Batch;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BatchController extends Controller
{
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
        return Inertia::render('Batch/BatchProfile', [
            'batch' => \App\Http\Resources\BatchResource::make($batch)->resolve(),
        ]);
    }

    /**
     * Store a newly created batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_number' => ['required', 'string', 'max:100', 'unique:batches,batch_number'],
            'warehouse_location' => ['required', 'string', 'max:255'],
            'quantity_bags' => ['required', 'integer', 'min:1'],
            'net_weight_kg' => ['required', 'numeric', 'min:1'],
            'moisture_content' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $batch = Batch::query()->create([
            'user_id' => $request->user()->id,
            'batch_number' => $validated['batch_number'],
            'warehouse_location' => $validated['warehouse_location'],
            'quantity' => $validated['quantity_bags'],
            'weight' => $validated['net_weight_kg'],
            'moisture_content' => $validated['moisture_content'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'received',
        ]);

        return redirect()
            ->route('batch.show', $batch)
            ->with('success', 'Batch added successfully.');
    }
}
