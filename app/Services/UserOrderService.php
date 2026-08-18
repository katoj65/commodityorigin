<?php

namespace App\Services;

use App\Models\AgriculturalInput;
use App\Models\Market;
use App\Models\Order;
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
     * @param  \Illuminate\Support\Collection<int, Order>  $orders  the real fulfillment
     *         Order created for each item, in the same order as $items — its id is
     *         kept on the receipt line so package tracking can read its live status
     */
    public function createFromCheckout(User $buyer, BaseCollection $items, string $paymentMethod, BaseCollection $orders): UserOrder
    {
        $lines = $items->values()->map(fn ($item, int $index) => [
            'market_id' => $item->cartable instanceof Market ? $item->cartable->id : null,
            'lot_code' => $this->referenceCodeFor($item->cartable),
            'name' => $this->nameFor($item->cartable),
            'unit' => $this->unitFor($item->cartable),
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'line_total' => round($item->quantity * $item->unit_price, 2),
            'order_id' => $orders->get($index)?->id,
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
     * Real-time package tracking for a purchase — reads the live fulfillment
     * status straight off the Order record each receipt line was linked to
     * at checkout. No tracking numbers or carrier data are invented; older
     * receipts created before this linkage existed simply report as
     * unavailable rather than showing fabricated progress.
     *
     * @return array<string, mixed>
     */
    public function trackingFor(UserOrder $userOrder): array
    {
        $lines = collect($userOrder->items);
        $orderIds = $lines->pluck('order_id')->filter()->unique()->values();

        if ($orderIds->isEmpty()) {
            return ['available' => false, 'stage' => null, 'steps' => [], 'items' => []];
        }

        $orders = Order::query()->whereIn('id', $orderIds)->get()->keyBy('id');

        $items = $lines->map(fn (array $line) => [
            'name' => $line['name'] ?? $line['lot_code'] ?? 'Lot',
            'lot_code' => $line['lot_code'] ?? null,
            'status' => $orders->get($line['order_id'] ?? null)?->status,
        ])->values();

        $stageRank = [
            'open' => 0, 'pending' => 1, 'confirmed' => 2, 'inspection' => 3,
            'processing' => 4, 'shipped' => 5, 'delivered' => 6,
        ];

        $statuses = $items->pluck('status')->filter()->values();

        if ($statuses->isEmpty()) {
            return ['available' => false, 'stage' => null, 'steps' => [], 'items' => []];
        }

        $terminal = $statuses->first(fn (string $s) => in_array($s, ['cancelled', 'withdrawn'], true));

        // The purchase's overall stage is the least-advanced status among
        // its items — a multi-lot package isn't "shipped" until every lot is.
        $stage = $terminal ?? $statuses->sortBy(fn (string $s) => $stageRank[$s] ?? 99)->first();
        $currentRank = $stageRank[$stage] ?? -1;

        $steps = collect(['confirmed', 'processing', 'shipped', 'delivered'])
            ->map(fn (string $step) => ['key' => $step, 'reached' => ($stageRank[$step] ?? 99) <= $currentRank])
            ->values()
            ->all();

        return [
            'available' => true,
            'stage' => $stage,
            'is_terminal' => in_array($stage, ['cancelled', 'withdrawn'], true),
            'steps' => $steps,
            'items' => $items->all(),
        ];
    }

    /**
     * A short reference code for a receipt line — a lot code for coffee,
     * a SKU for a store input.
     */
    private function referenceCodeFor(mixed $cartable): ?string
    {
        return match (true) {
            $cartable instanceof Market => $cartable->lot_code,
            $cartable instanceof AgriculturalInput => $cartable->sku,
            default => null,
        };
    }

    private function nameFor(mixed $cartable): string
    {
        return match (true) {
            $cartable instanceof Market => $cartable->name ?? $cartable->lot_code ?? 'Lot',
            $cartable instanceof AgriculturalInput => $cartable->name,
            default => 'Item',
        };
    }

    private function unitFor(mixed $cartable): string
    {
        return match (true) {
            $cartable instanceof Market => 'kg',
            $cartable instanceof AgriculturalInput => $cartable->unit,
            default => 'unit',
        };
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
