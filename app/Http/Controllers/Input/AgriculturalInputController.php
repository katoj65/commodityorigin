<?php

namespace App\Http\Controllers\Input;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AgriculturalInputResource;
use App\Models\AgriculturalInput;
use App\Services\AgriculturalInputService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AgriculturalInputController extends Controller
{
    public function __construct(private readonly AgriculturalInputService $inputs)
    {
    }

    /**
     * Display the agricultural input store. Every authenticated user may
     * browse it; only admins can add, edit, or remove inputs.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search')->value());
        $category = $request->string('category')->value() ?: null;

        $paginator = $this->inputs->paginateForList($search ?: null, $category);

        return Inertia::render('Farm/Inputs/Index', [
            'inputs' => [
                'data' => AgriculturalInputResource::collection($paginator->items())->resolve(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
            'stats' => $this->inputs->stats(),
            'tagOptions' => $this->inputs->tagOptions(),
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
            'canManage' => Gate::allows('create', AgriculturalInput::class),
        ]);
    }

    /**
     * Add a new input to the store. Admins only.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', AgriculturalInput::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(AgriculturalInputService::CATEGORIES)],
            'tag' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'image' => ImageUploadHelper::rules(),
        ]);

        $image = $request->file('image');
        unset($validated['image']);

        $this->inputs->store($validated, $request->user()->id, $image);

        return back()->with('success', 'Input added to the store successfully.');
    }

    /**
     * Update an existing input's listing. Admins only.
     */
    public function update(Request $request, AgriculturalInput $agriculturalInput): RedirectResponse
    {
        Gate::authorize('update', $agriculturalInput);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(AgriculturalInputService::CATEGORIES)],
            'tag' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'image' => ImageUploadHelper::rules(),
        ]);

        $image = $request->file('image');
        unset($validated['image']);

        $this->inputs->update($agriculturalInput, $validated, $image);

        return back()->with('success', 'Input updated successfully.');
    }

    /**
     * Remove an input from the store. Admins only.
     */
    public function destroy(AgriculturalInput $agriculturalInput): RedirectResponse
    {
        Gate::authorize('delete', $agriculturalInput);

        $this->inputs->delete($agriculturalInput);

        return back()->with('success', 'Input removed from the store successfully.');
    }
}
