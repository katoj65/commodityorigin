<?php

namespace App\Services;

use App\Models\AgriculturalInput;
use App\Models\CartItem;
use App\Models\Market;
use App\Models\Order;
use App\Models\User;
use App\Models\UserOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders,
        private readonly WalletService $wallets,
        private readonly EscrowService $escrow,
        private readonly UserOrderService $userOrders,
    ) {
    }

    /**
     * Place an order for every active item in the buyer's cart — one Order
     * per line item (a coffee lot or a store input), paid either from the
     * buyer's wallet (moved through escrow into the seller's wallet
     * immediately) or by card (no funds move through this app; card
     * details are never sent here beyond a masked last 4 digits for the
     * receipt).
     *
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>|null  $card
     */
    public function placeOrder(User $buyer, string $paymentMethod, array $delivery, ?array $card = null): UserOrder
    {
        $items = $this->cart->forUser($buyer->id);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $items = $items->values();

        return DB::transaction(function () use ($buyer, $paymentMethod, $delivery, $card, $items) {
            $orders = $items->map(fn (CartItem $item) => $this->placeItemOrder($buyer, $item, $paymentMethod, $delivery, $card));

            return $this->userOrders->createFromCheckout($buyer, $items, $paymentMethod, $orders);
        });
    }

    private function placeItemOrder(User $buyer, CartItem $item, string $paymentMethod, array $delivery, ?array $card): Order
    {
        $cartable = $item->cartable()->lockForUpdate()->first();

        if (! $cartable) {
            throw ValidationException::withMessages([
                'cart' => 'One of the items in your cart is no longer available for purchase.',
            ]);
        }

        $listing = $this->listingDetails($cartable);

        if (! $listing['seller_id']) {
            throw ValidationException::withMessages([
                'cart' => "\"{$listing['name']}\" has no seller on record and can't be purchased.",
            ]);
        }

        if ($item->quantity > $listing['available_quantity']) {
            throw ValidationException::withMessages([
                'cart' => "Only {$listing['available_quantity']} {$listing['unit']} of \"{$listing['name']}\" is left in stock.",
            ]);
        }

        $order = Order::query()->create([
            'order_number' => $this->orders->generateOrderNumber(),
            'type' => 'request',
            'buyer_id' => $buyer->id,
            'seller_id' => $listing['seller_id'],
            'crop_type' => $listing['crop_type'],
            'variety' => $listing['variety'],
            'grade' => $listing['grade'],
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'total_amount' => round($item->quantity * $item->unit_price, 2),
            'currency' => 'USD',
            'payment_method' => $paymentMethod,
            'notes' => $this->buildNotes($delivery, $paymentMethod, $card),
            'status' => 'confirmed',
        ]);

        if ($paymentMethod === 'wallet') {
            $this->wallets->ensureForUser($buyer->id);
            $this->wallets->ensureForUser($listing['seller_id']);
            $this->escrow->holdAndRelease($order, $buyer->id);
        }

        $this->decrementStock($cartable, $item->quantity);

        $item->update(['status' => CartItem::STATUS_ORDERED]);

        return $order;
    }

    /**
     * Normalize the fields CheckoutService needs from whatever purchasable
     * model a cart item points to, so placeItemOrder() doesn't have to
     * branch on type more than once.
     *
     * @return array{seller_id: ?int, name: string, crop_type: string, variety: ?string, grade: ?string, available_quantity: float, unit: string}
     */
    private function listingDetails(Market|AgriculturalInput $cartable): array
    {
        if ($cartable instanceof Market) {
            return [
                'seller_id' => $cartable->user_id,
                'name' => $cartable->title ?? $cartable->lot_code,
                'crop_type' => $cartable->type ?: 'Coffee',
                'variety' => $cartable->title ?? $cartable->lot_code,
                'grade' => $cartable->process,
                'available_quantity' => (float) $cartable->available_quantity,
                'unit' => $cartable->unit ?? 'kg',
            ];
        }

        return [
            'seller_id' => $cartable->user_id,
            'name' => $cartable->name,
            'crop_type' => 'agricultural_input:'.$cartable->category,
            'variety' => $cartable->tag,
            'grade' => null,
            'available_quantity' => (float) $cartable->stock_quantity,
            'unit' => $cartable->unit,
        ];
    }

    /**
     * Deduct the purchased quantity from whichever model was bought. A
     * Market listing that hits zero is marked sold; an AgriculturalInput
     * simply runs low — its own "active"/"inactive" listing status is a
     * separate, admin-managed concern, not tied to stock depth.
     */
    private function decrementStock(Market|AgriculturalInput $cartable, int $quantity): void
    {
        if ($cartable instanceof Market) {
            $cartable->decrement('available_quantity', $quantity);

            if ($cartable->fresh()->available_quantity <= 0) {
                $cartable->update(['status' => 'sold']);
            }

            return;
        }

        $cartable->decrement('stock_quantity', $quantity);
    }

    /**
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>|null  $card
     */
    private function buildNotes(array $delivery, string $paymentMethod, ?array $card): string
    {
        $address = collect([
            $delivery['address_line1'] ?? null,
            $delivery['address_line2'] ?? null,
            $delivery['city'] ?? null,
            $delivery['state'] ?? null,
            $delivery['country'] ?? null,
            $delivery['postal_code'] ?? null,
        ])->filter()->implode(', ');

        $lines = [
            "Deliver to {$delivery['full_name']} ({$delivery['phone']}), {$address}.",
            $paymentMethod === 'wallet'
                ? 'Paid from wallet balance.'
                : "Paid by card ending {$card['last4']}.",
        ];

        if (! empty($delivery['delivery_notes'])) {
            $lines[] = "Note: {$delivery['delivery_notes']}";
        }

        return implode(' ', $lines);
    }
}
