<?php

namespace App\Services;

use App\Models\CartItem;
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
     * per lot, paid either from the buyer's wallet (moved through escrow
     * into the seller's wallet immediately) or by card (no funds move
     * through this app; card details are never sent here beyond a masked
     * last 4 digits for the receipt).
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

        return DB::transaction(function () use ($buyer, $paymentMethod, $delivery, $card, $items) {
            foreach ($items as $item) {
                $this->placeItemOrder($buyer, $item, $paymentMethod, $delivery, $card);
            }

            return $this->userOrders->createFromCheckout($buyer, $items, $paymentMethod);
        });
    }

    private function placeItemOrder(User $buyer, CartItem $item, string $paymentMethod, array $delivery, ?array $card): Order
    {
        $market = $item->market()->lockForUpdate()->first();

        if (! $market) {
            throw ValidationException::withMessages([
                'cart' => 'One of the items in your cart is no longer available for purchase.',
            ]);
        }

        if (! $market->user_id) {
            throw ValidationException::withMessages([
                'cart' => "\"{$market->name}\" has no seller on record and can't be purchased.",
            ]);
        }

        if ($item->quantity > $market->quantity) {
            throw ValidationException::withMessages([
                'cart' => "Only {$market->quantity} kg of \"{$market->name}\" is left in stock.",
            ]);
        }

        $order = Order::query()->create([
            'order_number' => $this->orders->generateOrderNumber(),
            'type' => 'request',
            'buyer_id' => $buyer->id,
            'seller_id' => $market->user_id,
            'crop_type' => $market->type,
            'variety' => $market->name,
            'grade' => $market->process,
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
            $this->wallets->ensureForUser($market->user_id);
            $this->escrow->holdAndRelease($order, $buyer->id);
        }

        $market->decrement('quantity', $item->quantity);

        if ($market->fresh()->quantity <= 0) {
            $market->update(['status' => 'sold']);
        }

        $item->update(['status' => CartItem::STATUS_ORDERED]);

        return $order;
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
