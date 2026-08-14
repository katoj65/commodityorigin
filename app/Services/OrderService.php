<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderIntent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Get a base query builder for orders.
     */
    public function query(): Builder
    {
        return Order::query();
    }

    /**
     * Get every order a user is party to, as buyer or seller, newest first.
     *
     * @return Collection<int, Order>
     */
    public function ordersForUser(int $userId): Collection
    {
        return Order::query()
            ->with(['buyer', 'seller'])
            ->where(fn (Builder $query) => $query->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->latest()
            ->get();
    }

    /**
     * Get specific orders belonging to a buyer, by ID — used to render the
     * post-checkout confirmation page for exactly the orders just placed.
     *
     * @param  array<int, int>  $ids
     * @return Collection<int, Order>
     */
    public function forBuyerAndIds(int $buyerId, array $ids): Collection
    {
        if (empty($ids)) {
            return new Collection();
        }

        return Order::query()
            ->with('seller')
            ->where('buyer_id', $buyerId)
            ->whereIn('id', $ids)
            ->latest()
            ->get();
    }

    /**
     * Get every order posted by other users — requests and offers, at any
     * status — visible to the whole marketplace, not just their parties.
     *
     * @return Collection<int, Order>
     */
    public function openOrdersExcluding(int $userId): Collection
    {
        return Order::query()
            ->with(['buyer', 'seller'])
            ->where(fn (Builder $query) => $query->where('buyer_id', '!=', $userId)->orWhereNull('buyer_id'))
            ->where(fn (Builder $query) => $query->where('seller_id', '!=', $userId)->orWhereNull('seller_id'))
            ->latest()
            ->get();
    }

    /**
     * Post a new open order — a "request" is created on behalf of a buyer
     * with no seller yet, an "offer" is created on behalf of a seller with
     * no buyer yet.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $userId): Order
    {
        $quantity = (float) $data['quantity'];
        $unitPrice = (float) $data['unit_price'];
        $type = $data['type'] ?? 'request';

        return Order::query()
            ->create([
                ...$data,
                'order_number' => $this->generateOrderNumber(),
                'type' => $type,
                'buyer_id' => $type === 'request' ? $userId : null,
                'seller_id' => $type === 'offer' ? $userId : null,
                'total_amount' => round($quantity * $unitPrice, 2),
                'status' => 'open',
            ])
            ->refresh();
    }

    /**
     * Get a base query builder for order intents.
     */
    public function intentsQuery(): Builder
    {
        return OrderIntent::query();
    }

    /**
     * Get every intent submitted on an order, newest first.
     *
     * @return Collection<int, OrderIntent>
     */
    public function intentsForOrder(Order $order): Collection
    {
        return $order->intents()->with('user.profile')->latest()->get();
    }

    /**
     * Express intent to fulfill an open order — a seller on a request, or
     * a buyer on an offer. Bumps the order to "pending" on its first
     * intent so its creator knows activity has begun, without locking out
     * any other interested party.
     */
    public function createIntent(Order $order, int $userId): OrderIntent
    {
        $intent = $order->intents()->create([
            'user_id' => $userId,
            'status' => 'pending',
        ]);

        if ($order->status === 'open') {
            $order->update(['status' => 'pending']);
        }

        return $intent;
    }

    /**
     * Update an existing order intent.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateIntent(OrderIntent $intent, array $data): OrderIntent
    {
        $intent->update($data);

        return $intent;
    }

    /**
     * Delete an order intent — used when a party withdraws it.
     */
    public function destroyIntent(OrderIntent $intent): void
    {
        $intent->delete();
    }

    /**
     * Confirm one intent as the order's counterparty — the seller on a
     * request, the buyer on an offer — and decline every other pending
     * intent on the order.
     */
    public function confirmIntent(Order $order, OrderIntent $intent): Order
    {
        $order->update([
            ...($order->type === 'offer' ? ['buyer_id' => $intent->user_id] : ['seller_id' => $intent->user_id]),
            'status' => 'confirmed',
        ]);

        $this->updateIntent($intent, ['status' => 'confirmed']);

        $order->intents()
            ->where('id', '!=', $intent->id)
            ->where('status', 'pending')
            ->update(['status' => 'declined']);

        return $order->refresh();
    }

    /**
     * Withdraw an order. If it's still open — nobody has expressed
     * intent, so there's no history to preserve — it's deleted outright.
     * Otherwise it's marked as withdrawn so the record and its history
     * remain.
     */
    public function withdraw(Order $order): void
    {
        if ($order->status === 'open') {
            $order->delete();

            return;
        }

        $order->update(['status' => 'withdrawn']);
    }

    /**
     * Update an existing order, recalculating the total when quantity or
     * unit price change.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order
    {
        if (array_key_exists('quantity', $data) || array_key_exists('unit_price', $data)) {
            $quantity = (float) ($data['quantity'] ?? $order->quantity);
            $unitPrice = (float) ($data['unit_price'] ?? $order->unit_price);
            $data['total_amount'] = round($quantity * $unitPrice, 2);
        }

        $order->update($data);

        return $order;
    }

    /**
     * Delete an order.
     */
    public function destroy(Order $order): void
    {
        $order->delete();
    }

    /**
     * Rank buyers by total order value across every order they've placed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function topBuyers(int $limit = 5): array
    {
        $orders = Order::query()->whereNotNull('buyer_id')->with('buyer')->get();

        if ($orders->isEmpty()) {
            return [];
        }

        return $orders->groupBy('buyer_id')
            ->map(function (Collection $group): array {
                $buyer = $group->first()->buyer;

                return [
                    'name' => $buyer?->name ?? 'Buyer',
                    'orders' => $group->count(),
                    'quantity_kg' => (float) $group->sum('quantity'),
                    'total_value' => (float) $group->sum('total_amount'),
                ];
            })
            ->sortByDesc('total_value')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Rank sellers by total order value across every order they've fulfilled.
     *
     * @return array<int, array<string, mixed>>
     */
    public function topSellers(int $limit = 5): array
    {
        $orders = Order::query()->whereNotNull('seller_id')->with('seller')->get();

        if ($orders->isEmpty()) {
            return [];
        }

        return $orders->groupBy('seller_id')
            ->map(function (Collection $group): array {
                $seller = $group->first()->seller;

                return [
                    'name' => $seller?->name ?? 'Seller',
                    'orders' => $group->count(),
                    'quantity_kg' => (float) $group->sum('quantity'),
                    'total_value' => (float) $group->sum('total_amount'),
                ];
            })
            ->sortByDesc('total_value')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Summarize a user's trade history on this marketplace — completed,
     * active, and cancelled order counts, as either buyer or seller — so
     * the other party can review it before confirming them.
     *
     * @return array{completed: int, active: int, cancelled: int, total: int}
     */
    public function tradeHistorySummary(int $userId): array
    {
        $counts = Order::query()
            ->where(fn (Builder $query) => $query->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'completed' => (int) ($counts['delivered'] ?? 0),
            'active' => (int) (($counts['confirmed'] ?? 0) + ($counts['processing'] ?? 0) + ($counts['shipped'] ?? 0)),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
            'total' => (int) $counts->sum(),
        ];
    }

    /**
     * Generate a unique, human-readable order number.
     */
    public function generateOrderNumber(): string
    {
        do {
            $candidate = 'ORD-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $candidate)->exists());

        return $candidate;
    }
}
