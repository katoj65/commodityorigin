<?php

namespace App\Http\Controllers\Rfq;

use App\Http\Controllers\Controller;
use App\Models\CropGradeMetadata;
use App\Models\CropVarietyMetadata;
use App\Models\LotRequest;
use App\Services\LotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RfqController extends Controller
{
    public function __construct(
        private readonly LotService $lots,
    ) {
    }

    /**
     * Display the request-for-quote list.
     */
    public function index(): Response
    {
        return Inertia::render('Rfq/Index', [
            'requests' => LotRequest::query()->with('user')->latest()->get(),
            'cropTypes' => CropVarietyMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'grades' => CropGradeMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
        ]);
    }

    /**
     * Store a new request for quote.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crop_type' => ['required', 'string', 'max:255', Rule::exists('crop_variety_metadata', 'name')->where('is_active', true)],
            'variety' => ['nullable', 'string', 'max:255'],
            'grade' => ['required', 'string', 'max:255', Rule::exists('crop_grade_metadata', 'name')->where('is_active', true)],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->lots->createRequest($validated, $request->user()->id);

        return back()->with('success', 'Request for quote submitted.');
    }

    /**
     * Remove a request for quote.
     */
    public function destroy(LotRequest $lotRequest): RedirectResponse
    {
        $this->lots->destroyRequest($lotRequest);

        return back()->with('success', 'Request for quote removed.');
    }
}
