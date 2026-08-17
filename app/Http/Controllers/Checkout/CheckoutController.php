<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
        private readonly WalletService $wallets,
        private readonly OrderService $orders,
    ) {
    }

    /**
     * Display the current user's cart.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Checkout/CartPage', [
            'items' => CartItemResource::collection(
                $this->cart->forUser($request->user()->id),
            )->resolve(),
        ]);
    }

    /**
     * Add a market listing to the current user's cart, or increase its
     * quantity if it's already there.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $this->cart->addItem($request->user()->id, $validated['market_id'], $validated['quantity'] ?? 1);

        return back()->with('success', 'Added to cart.');
    }

    /**
     * Update a cart item's quantity. Owner only.
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $this->cart->updateQuantity($request->user()->id, $cartItem, $validated['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove an item from the cart. Owner only.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->cart->removeItem($request->user()->id, $cartItem);

        return back()->with('success', 'Removed from cart.');
    }

    /**
     * Display the checkout page — delivery and payment details for the
     * items currently in the cart. Redirects back to the cart if it's
     * empty, since there's nothing to check out.
     */
    public function confirmation(Request $request): Response|RedirectResponse
    {
        $userId = $request->user()->id;
        $items = $this->cart->forUser($userId);

        if ($items->isEmpty()) {
            return redirect()->route('checkout.index')->with('error', 'Your cart is empty.');
        }

        $profile = $request->user()->profile;

        return Inertia::render('Checkout/Confirmation', [
            'items' => CartItemResource::collection($items)->resolve(),
            'walletBalance' => (float) ($this->wallets->forUser($userId)?->availableBalance() ?? 0),
            'deliveryDefaults' => [
                'full_name' => $request->user()->name,
                'phone' => $request->user()->telephone,
                'address_line1' => $profile?->address_line_1,
                'address_line2' => $profile?->address_line_2,
                'city' => $profile?->city,
                'state' => $profile?->state,
                'country' => $profile?->country,
                'postal_code' => $profile?->postal_code,
            ],
        ]);
    }

    /**
     * Place an order for every item in the cart, paid either from the
     * buyer's wallet or by card.
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['wallet', 'card'])],
            'delivery' => ['required', 'array'],
            'delivery.full_name' => ['required', 'string', 'max:255'],
            'delivery.phone' => ['required', 'string', 'max:30'],
            'delivery.address_line1' => ['required', 'string', 'max:255'],
            'delivery.address_line2' => ['nullable', 'string', 'max:255'],
            'delivery.city' => ['required', 'string', 'max:120'],
            'delivery.state' => ['nullable', 'string', 'max:120'],
            'delivery.country' => ['required', 'string', 'max:120'],
            'delivery.postal_code' => ['required', 'string', 'max:20'],
            'delivery.delivery_notes' => ['nullable', 'string', 'max:500'],
            'card' => ['required_if:payment_method,card', 'nullable', 'array'],
            'card.holder' => ['required_if:payment_method,card', 'nullable', 'string', 'max:255'],
            'card.last4' => ['required_if:payment_method,card', 'nullable', 'digits:4'],
            'card.brand' => ['nullable', 'string', 'max:30'],
            'card.expiry' => ['required_if:payment_method,card', 'nullable', 'string', 'max:5'],
        ]);

        $purchase = $this->checkout->placeOrder(
            $request->user(),
            $validated['payment_method'],
            $validated['delivery'],
            $validated['card'] ?? null,
        );

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Order placed successfully.');
    }

    /**
     * Display the post-checkout confirmation page for the orders just
     * placed. Orders are looked up by ID (scoped to the current buyer) so
     * the page survives a refresh instead of relying on flashed session
     * data that would vanish after one request.
     */
    public function orderConfirmed(Request $request): Response|RedirectResponse
    {
        $ids = collect((array) $request->query('orders', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        $orders = $this->orders->forBuyerAndIds($request->user()->id, $ids);

        if ($orders->isEmpty()) {
            return redirect()->route('orders.index');
        }

        return Inertia::render('Checkout/OrderConfirmed', [
            'orders' => OrderResource::collection($orders)->resolve(),
        ]);
    }
}
