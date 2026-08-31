<?php

namespace App\Services;

use App\Models\AgriculturalInput;
use App\Models\CartItem;
use App\Models\Market;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartService
{
    /**
     * Short type keys accepted from the frontend, mapped to the model
     * class each one points to. Keeping this whitelist here (rather than
     * accepting a raw class name from the client) is what lets the cart
     * stay polymorphic without turning `cartable_type` into an
     * attacker-controlled class-instantiation vector.
     *
     * @var array<string, class-string<Model>>
     */
    public const CARTABLE_TYPES = [
        'market' => Market::class,
        'agricultural_input' => AgriculturalInput::class,
    ];

    /**
     * Get a base query builder for cart items.
     */
    public function query(): Builder
    {
        return CartItem::query();
    }

    /**
     * Get every active item in a user's cart, with its purchasable model
     * (and that model's own seller/creator), newest first.
     *
     * @return Collection<int, CartItem>
     */
    public function forUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->active()
            ->with(['cartable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Market::class => ['user'],
                    AgriculturalInput::class => ['creator'],
                ]);
            }])
            ->latest()
            ->get();
    }

    /**
     * Count how many active items a user has in their cart, for the
     * header cart badge.
     */
    public function activeCountForUser(int $userId): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->active()
            ->count();
    }

    /**
     * Get the quantity a user already has in their cart for a given
     * purchasable item, or 0 if it isn't in their cart.
     */
    public function quantityFor(int $userId, string $cartableType, int $cartableId): int
    {
        return (int) ($this->query()
            ->where('user_id', $userId)
            ->where('cartable_type', $this->modelClassFor($cartableType))
            ->where('cartable_id', $cartableId)
            ->active()
            ->value('quantity') ?? 0);
    }

    /**
     * Add a purchasable item to a user's cart, or increase its quantity if
     * it's already there.
     */
    public function addItem(int $userId, string $cartableType, int $cartableId, int $quantity = 1): CartItem
    {
        $modelClass = $this->modelClassFor($cartableType);
        $cartable = $modelClass::query()->findOrFail($cartableId);

        $item = $this->query()
            ->where('user_id', $userId)
            ->where('cartable_type', $modelClass)
            ->where('cartable_id', $cartable->id)
            ->active()
            ->first();

        if ($item) {
            $item->update(['quantity' => $item->quantity + $quantity]);

            return $item;
        }

        return CartItem::query()->create([
            'user_id' => $userId,
            'cartable_id' => $cartable->id,
            'cartable_type' => $modelClass,
            'quantity' => $quantity,
            'unit_price' => $this->unitPriceFor($cartable),
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

    /**
     * Resolve a short, client-facing type key to its model class.
     *
     * @return class-string<Model>
     */
    public function modelClassFor(string $type): string
    {
        return self::CARTABLE_TYPES[$type] ?? abort(422, 'Unsupported cart item type.');
    }

    /**
     * The per-unit price to lock in when a purchasable item is first added
     * to the cart — each model prices itself differently (coffee lots by
     * the kg, store inputs by their own unit).
     */
    private function unitPriceFor(Model $cartable): float
    {
        return (float) ($cartable instanceof Market ? $cartable->price_per_unit : $cartable->price);
    }
}
