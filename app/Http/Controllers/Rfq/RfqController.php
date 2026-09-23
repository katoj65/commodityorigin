<?php

namespace App\Http\Controllers\Rfq;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\CommodityOriginMetadata;
use App\Models\CropGradeMetadata;
use App\Models\CropVarietyMetadata;
use App\Models\IncotermMetadata;
use App\Models\LotRequest;
use App\Models\MarketMetadata;
use App\Models\PaymentMetadata;
use App\Models\SettingsRfqSpecification;
use App\Services\LotService;
use App\Services\MarketService;
use App\Services\SettingsRfqSpecificationService;
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
        private readonly SettingsRfqSpecificationService $specifications,
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
            'origins' => CommodityOriginMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'incoterms' => IncotermMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'paymentTerms' => PaymentMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'destinations' => MarketMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'marketCount' => $this->market->liveCount(),
            'auctionCount' => Auction::query()->count(),
            'requestCount' => LotRequest::query()->count(),
            'latestSpecification' => SettingsRfqSpecification::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->first(),
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
            'origin' => ['nullable', 'string', 'max:255'],
            'incoterm' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'string', 'max:255'],
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

    /**
     * Store a new RFQ specification from the sourcing desk.
     */
    public function storeSpecification(Request $request): RedirectResponse
    {
        $this->specifications->create($this->validateSpecification($request), $request->user()->id);

        return back()->with('success', 'RFQ specification saved.');
    }

    /**
     * Update an existing RFQ specification.
     */
    public function updateSpecification(Request $request, SettingsRfqSpecification $specification): RedirectResponse
    {
        abort_unless($specification->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $this->specifications->update($specification, $this->validateSpecification($request));

        return back()->with('success', 'RFQ specification updated.');
    }

    /**
     * Validate the shared RFQ specification payload.
     *
     * @return array<string, mixed>
     */
    private function validateSpecification(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'string', 'max:255', Rule::exists('crop_variety_metadata', 'name')->where('is_active', true)],
            'grade' => ['required', 'string', 'max:255', Rule::exists('crop_grade_metadata', 'name')->where('is_active', true)],
            'target_price' => ['required', 'numeric', 'min:0'],
            'destination' => ['required', 'string', 'max:255', Rule::exists('market_metadata', 'name')->where('is_active', true)],
            'payment_terms' => ['required', 'string', 'max:255', Rule::exists('payment_metadata', 'name')->where('is_active', true)],
            'min_weight' => ['required', 'numeric', 'min:0'],
            'max_weight' => ['required', 'numeric', 'min:0'],
            'incoterms' => ['required', 'string', 'max:255', Rule::exists('incoterm_metadata', 'name')->where('is_active', true)],
        ]);
    }
}
