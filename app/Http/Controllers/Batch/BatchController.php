<?php

namespace App\Http\Controllers\Batch;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BatchController extends Controller
{
    /**
     * Show the batch creation form.
     */
    public function create(): Response
    {
        return Inertia::render('Batch/Create', [
            'statusOptions' => ['received', 'in_storage', 'ready', 'released'],
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
            'status' => ['required', 'string', Rule::in(['received', 'in_storage', 'ready', 'released'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Batch::query()->create($validated);

        return redirect()
            ->route('batch.create')
            ->with('success', 'Batch added successfully.');
    }
}
