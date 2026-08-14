<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Market;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    /**
     * Get a base query builder for cart items.
     */
    public function query(): Builder
    {
        return CartItem::query();
    }

    /**
     * Get every active item in a user's cart, with its market listing,
     * newest first.
     *
     * @return Collection<int, CartItem>
     */
    public function forUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->active()
            ->with('market.user')
            ->latest()
            ->get();
    }

    /**
     * Count how many active lots a user has in their cart, for the header
     * cart badge.
     */
    public function activeCountForUser(int $userId): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->active()
            ->count();
    }

    /**
     * Get the quantity a user already has in their cart for a given market
     * listing, or 0 if it isn't in their cart.
     */
    public function quantityFor(int $userId, int $marketId): int
    {
        return (int) ($this->query()
            ->where('user_id', $userId)
            ->where('market_id', $marketId)
            ->active()
            ->value('quantity') ?? 0);
    }

    /**
     * Add a market listing to a user's cart, or increase its quantity if
     * it's already there.
     */
    public function addItem(int $userId, int $marketId, int $quantity = 1): CartItem
    {
        $market = Market::query()->findOrFail($marketId);

        $item = $this->query()
            ->where('user_id', $userId)
            ->where('market_id', $market->id)
            ->active()
            ->first();

        if ($item) {
            $item->update(['quantity' => $item->quantity + $quantity]);

            return $item;
        }

        return CartItem::query()->create([
            'user_id' => $userId,
            'market_id' => $market->id,
            'quantity' => $quantity,
            'unit_price' => $market->price_per_kg,
            'status' => CartItem::STATUS_ACTIVE,
        ]);
    }

    /**
     * Set a cart item's quantity. Only the item's owner may update it.
     */
    public function updateQuantity(int $userId, CartItem $item, int $quantity): CartItem
    {
        abort_unless($item->user_id === $userId, 403);

        $item->update(['quantity' => $quantity]);

        return $item;
    }

    /**
     * Remove an item from a cart. Only the item's owner may remove it.
     */
    public function removeItem(int $userId, CartItem $item): void
    {
        abort_unless($item->user_id === $userId, 403);

        $item->delete();
    }
}
