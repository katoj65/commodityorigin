<?php

namespace App\Http\Controllers\Rfq;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\CropGradeMetadata;
use App\Models\CropVarietyMetadata;
use App\Models\LotRequest;
use App\Services\LotService;
use App\Services\MarketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RfqController extends Controller
{
    public function __construct(
        private readonly LotService $lots,
        private readonly MarketService $market,
    ) {
    }

    /**
     * Display the request-for-quote list — wrapped in the same TradeLayout
     * shell as the rest of the Trade hub, so its tab bar's counts need the
     * same real Market/Auction/LotRequest figures TradeController::index()
     * uses.
     */
    public function index(Request $request): Response
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
            'marketCount' => $this->market->liveCount(),
            'auctionCount' => Auction::query()->count(),
            'requestCount' => LotRequest::query()->count(),
            'authUserId' => $request->user()->id,
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
     * Remove a request for quote. Only its owner (or an admin) may delete
     * it — mirrors LotRequestPolicy::delete(), the same rule the
     * lot.request.destroy route already enforces for this model.
     */
    public function destroy(Request $request, LotRequest $lotRequest): RedirectResponse
    {
        abort_unless($request->user()->isAdmin() || $lotRequest->user_id === $request->user()->id, 403);

        $this->lots->destroyRequest($lotRequest);

        return back()->with('success', 'Request for quote removed.');
    }
}
