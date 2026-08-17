<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Validation\ValidationException;

class UserOrderService
{
    /**
     * Get every purchase receipt for a user, newest first.
     *
     * @return Collection<int, UserOrder>
     */
    public function forUser(int $userId): Collection
    {
        return UserOrder::query()
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Record a purchase receipt for a completed checkout — one receipt per
     * checkout event, summarizing every item that was just bought. Called
     * from inside CheckoutService's transaction, so it's atomic with the
     * underlying Order records.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CartItem>  $items
     */
    public function createFromCheckout(User $buyer, BaseCollection $items, string $paymentMethod): UserOrder
    {
        $lines = $items->map(fn ($item) => [
            'market_id' => $item->market_id,
            'lot_code' => $item->market?->lot_code,
            'name' => $item->market?->name ?? $item->market?->lot_code ?? 'Lot',
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'line_total' => round($item->quantity * $item->unit_price, 2),
        ]);

        return UserOrder::query()->create([
            'user_id' => $buyer->id,
            'order_number' => $this->generateOrderNumber(),
            'items' => $lines->values()->all(),
            'total_amount' => round($lines->sum('line_total'), 2),
            'currency' => 'USD',
            'payment_method' => $paymentMethod,
            'status' => 'completed',
        ]);
    }

    /**
     * Cancel a purchase receipt. Purely a record-keeping status change for
     * the buyer's own history — it does not reverse the underlying Order,
     * escrow, or inventory effects of the original purchase.
     */
    public function cancel(UserOrder $order): UserOrder
    {
        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages(['status' => 'This order is already cancelled.']);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $order;
    }

    /**
     * Generate a unique, human-readable purchase reference.
     */
    private function generateOrderNumber(): string
    {
        do {
            $candidate = 'PUR-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (UserOrder::query()->where('order_number', $candidate)->exists());

        return $candidate;
    }
}
