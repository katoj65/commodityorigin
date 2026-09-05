<?php

namespace App\Http\Controllers\Trade;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Resources\OfferResponseResource;
use App\Helpers\WalletTransferHelper;
use App\Models\Auction;
use App\Models\LotRequest;
use App\Models\Offer;
use App\Models\OfferResponse;
use App\Services\MarketService;
use App\Services\OfferPaymentService;
use App\Services\OfferService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TradeController extends Controller
{
    public function __construct(
        private readonly MarketService $market,
        private readonly OfferService $offers,
        private readonly OfferPaymentService $payments,
        private readonly WalletService $wallet,
    ) {
    }

    /**
     * Display the trade hub.
     */
    public function index(): Response
    {
        return Inertia::render('Trade/Index', [
            'markets' => $this->market->marketPageListing(),
            'auctionCount' => Auction::query()->count(),
            'requestCount' => LotRequest::query()->count(),
        ]);
    }

    /**
     * Display the offers board — every open sell-side offer on the
     * marketplace, plus the responses the user has received on their own
     * offers.
     */
    public function offer(Request $request): Response
    {
        $userId = $request->user()->id;

        $received = OfferResponse::query()
            ->whereHas('offer', fn ($query) => $query->where('seller_id', $userId))
            ->with(['offer', 'user', 'owner'])
            ->latest()
            ->get();

        $sent = OfferResponse::query()
            ->where('user_id', $userId)
            ->with(['offer', 'user', 'owner'])
            ->latest()
            ->get();

        return Inertia::render('Trade/Offer', [
            'offers' => OfferResource::collection($this->offers->available())->resolve(),
            'myOfferResponses' => OfferResponseResource::collection($received)->resolve(),
            'myResponses' => OfferResponseResource::collection($sent)->resolve(),
            'authUserId' => $userId,
        ]);
    }

    /**
     * Post a new sell-side offer.
     */
    public function storeOffer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crop_type' => ['required', 'string', 'max:255'],
            'variety' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->offers->create($validated, $request->user()->id);

        return back()->with('success', 'Offer posted — waiting for buyers to express interest.');
    }

    /**
     * Update an existing offer. Only its seller may edit it.
     */
    public function updateOffer(Request $request, Offer $offer): RedirectResponse
    {
        $this->authorizeSeller($offer, $request->user()->id);

        $validated = $request->validate([
            'crop_type' => ['required', 'string', 'max:255'],
            'variety' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->offers->update($offer, $validated);

        return back()->with('success', 'Offer updated.');
    }

    /**
     * Delete an offer. Only its seller may delete it.
     */
    public function destroyOffer(Request $request, Offer $offer): RedirectResponse
    {
        $this->authorizeSeller($offer, $request->user()->id);

        $this->offers->destroy($offer);

        return back()->with('success', 'Offer deleted.');
    }

    /**
     * Respond to an offer as a buyer. The seller cannot respond to their
     * own offer.
     */
    public function storeOfferResponse(Request $request, Offer $offer): RedirectResponse
    {
        abort_if($offer->seller_id === $request->user()->id, 403, 'You cannot respond to your own offer.');

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $offer->responses()->create([
            'user_id' => $request->user()->id,
            'order_owner_id' => $offer->seller_id,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Interest expressed — the seller will review your response.');
    }

    /**
     * Accept or decline a response. Only the offer's seller may act.
     */
    public function updateOfferResponse(Request $request, OfferResponse $offerResponse): RedirectResponse
    {
        $this->authorizeSeller($offerResponse->offer, $request->user()->id);

        $validated = $request->validate([
            'status' => ['required', 'in:accepted,declined'],
        ]);

        $offerResponse->update(['status' => $validated['status']]);

        return back()->with('success', 'Response ' . $validated['status'] . '.');
    }

    /**
     * Display the payment page for an offer.
     */
    public function showOfferPayment(Request $request, Offer $offer): Response
    {
        $offer->load(['seller', 'responses' => fn ($query) => $query->latest()]);

        $response = $offer->responses
            ->where('user_id', $request->user()->id)
            ->first();

        $wallet = $this->wallet->forUser($request->user()->id);

        return Inertia::render('Trade/OfferPayment', [
            'offer' => OfferResource::make($offer)->resolve(),
            'response' => $response ? OfferResponseResource::make($response)->resolve() : null,
            'walletBalance' => $wallet ? (float) $wallet->availableBalance() : 0,
        ]);
    }

    /**
     * Submit payment for an offer. Marks the offer pending so it leaves the
     * open offers board.
     */
    public function storeOfferPayment(Request $request, Offer $offer): RedirectResponse
    {
        abort_if($offer->seller_id === $request->user()->id, 403, 'You cannot pay for your own offer.');

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $userId = $request->user()->id;

        // Pay by wallet — transfer from the buyer's wallet to the seller's.
        if (strtolower($validated['payment_method']) === 'wallet') {
            $buyerWallet = $this->wallet->ensureForUser($userId);
            $sellerWallet = $this->wallet->ensureForUser($offer->seller_id);

            WalletTransferHelper::transfer(
                $buyerWallet,
                $sellerWallet,
                (string) $offer->total_amount,
                null,
                "Payment for offer {$offer->offer_number}",
            );
        }

        $this->payments->create($offer, $userId, $validated);

        // Mark the buyer's response as paid.
        $offer->responses()
            ->where('user_id', $userId)
            ->latest()
            ->first()
            ?->update(['status' => 'paid']);

        // The offer is now bought, so it leaves the open offers board.
        $offer->update([
            'status' => 'bought',
            'buyer_id' => $userId,
        ]);

        return redirect()->route('trade.offer')->with('success', 'Payment received — the offer is now marked as bought.');
    }

    /**
     * Ensure the acting user is the offer's seller.
     */
    private function authorizeSeller(Offer $offer, int $userId): void
    {
        abort_unless($offer->seller_id === $userId, 403);
    }
}
