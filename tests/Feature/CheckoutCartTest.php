<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Market;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutCartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A full, valid checkout delivery payload.
     *
     * @return array<string, mixed>
     */
    private function validDelivery(): array
    {
        return [
            'full_name' => 'Jane Buyer',
            'phone' => '+15550000000',
            'address_line1' => '1 Market Street',
            'city' => 'Kampala',
            'country' => 'Uganda',
            'postal_code' => '00100',
        ];
    }

    public function test_placing_a_card_order_charges_the_real_market_price_and_decrements_available_quantity(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $market = Market::query()->create([
            'user_id' => $seller->id,
            'title' => 'Bugisu AA Premium',
            'quantity' => 300,
            'available_quantity' => 100,
            'unit' => 'kg',
            'price_per_unit' => 7.5,
            'status' => 'live',
            'metadata' => ['type' => 'Arabica'],
        ]);

        app(CartService::class)->addItem($buyer->id, 'market', $market->id, 10);

        $userOrder = app(CheckoutService::class)->placeOrder($buyer, 'card', $this->validDelivery(), [
            'holder' => 'Jane Buyer',
            'last4' => '4242',
            'brand' => 'visa',
            'expiry' => '12/29',
        ]);

        $this->assertSame('completed', $userOrder->status);
        $this->assertSame(75.0, (float) $userOrder->total_amount);

        $line = $userOrder->items[0];
        $this->assertSame('Bugisu AA Premium', $line['name']);
        $this->assertSame('kg', $line['unit']);
        $this->assertSame(7.5, (float) $line['unit_price']);
        $this->assertSame(75.0, (float) $line['line_total']);

        $order = \App\Models\Order::query()->findOrFail($line['order_id']);
        $this->assertSame($seller->id, $order->seller_id);
        $this->assertSame('Arabica', $order->crop_type);
        $this->assertSame('Bugisu AA Premium', $order->variety);
        $this->assertSame(7.5, (float) $order->unit_price);
        $this->assertSame(75.0, (float) $order->total_amount);

        $market->refresh();
        $this->assertSame(90.0, (float) $market->available_quantity);
        $this->assertSame('live', $market->status);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $buyer->id,
            'cartable_id' => $market->id,
            'status' => 'ordered',
        ]);
    }

    public function test_placing_an_order_that_exhausts_stock_marks_the_listing_sold(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $market = Market::query()->create([
            'user_id' => $seller->id,
            'title' => 'Last Bag Lot',
            'quantity' => 50,
            'available_quantity' => 50,
            'unit' => 'kg',
            'price_per_unit' => 5,
            'status' => 'live',
        ]);

        app(CartService::class)->addItem($buyer->id, 'market', $market->id, 50);

        app(CheckoutService::class)->placeOrder($buyer, 'card', $this->validDelivery(), [
            'holder' => 'Jane Buyer',
            'last4' => '4242',
            'expiry' => '12/29',
        ]);

        $market->refresh();
        $this->assertSame(0.0, (float) $market->available_quantity);
        $this->assertSame('sold', $market->status);
    }

    public function test_placing_a_wallet_order_moves_funds_from_buyer_to_seller_through_escrow(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $market = Market::query()->create([
            'user_id' => $seller->id,
            'title' => 'Wallet Paid Lot',
            'quantity' => 100,
            'available_quantity' => 40,
            'unit' => 'kg',
            'price_per_unit' => 4,
            'status' => 'live',
        ]);

        app(WalletService::class)->deposit($buyer->id, '500.00');
        app(CartService::class)->addItem($buyer->id, 'market', $market->id, 10);

        $userOrder = app(CheckoutService::class)->placeOrder($buyer, 'wallet', $this->validDelivery());

        $this->assertSame('completed', $userOrder->status);
        $this->assertSame(40.0, (float) $userOrder->total_amount);

        $buyerWallet = app(WalletService::class)->forUser($buyer->id);
        $sellerWallet = app(WalletService::class)->forUser($seller->id);

        $this->assertSame('460.00', (string) $buyerWallet->balance);
        $this->assertSame('40.00', (string) $sellerWallet->balance);

        $market->refresh();
        $this->assertSame(30.0, (float) $market->available_quantity);
    }

    public function test_the_full_add_to_cart_through_checkout_http_flow_works(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $market = Market::query()->create([
            'user_id' => $seller->id,
            'title' => 'HTTP Flow Lot',
            'quantity' => 200,
            'available_quantity' => 60,
            'unit' => 'kg',
            'price_per_unit' => 6,
            'status' => 'live',
        ]);

        // Add to cart, exactly like the market listings grid / product page do.
        $this->actingAs($buyer)->post(route('checkout.items.store'), [
            'cartable_type' => 'market',
            'cartable_id' => $market->id,
            'quantity' => 5,
        ])->assertSessionHasNoErrors();

        // The cart page reflects the real price and name.
        $this->actingAs($buyer)->get(route('checkout.index'))
            ->assertInertia(fn ($page) => $page
                ->where('items.0.name', 'HTTP Flow Lot')
                ->where('items.0.unit_price', 6)
                ->where('items.0.line_total', 30)
                ->where('items.0.available_quantity', 60)
            );

        // Bump the quantity from the cart page.
        $cartItem = CartItem::where('user_id', $buyer->id)->where('cartable_id', $market->id)->firstOrFail();
        $this->actingAs($buyer)->patch(route('checkout.items.update', $cartItem), ['quantity' => 8])
            ->assertSessionHasNoErrors();
        $this->assertSame(8, $cartItem->fresh()->quantity);

        // Checkout confirmation shows the same numbers.
        $this->actingAs($buyer)->get(route('checkout.confirmation'))
            ->assertInertia(fn ($page) => $page
                ->where('items.0.line_total', 48)
            );

        // Place the order for real, by card, through the real endpoint.
        $response = $this->actingAs($buyer)->post(route('checkout.placeOrder'), [
            'payment_method' => 'card',
            'delivery' => $this->validDelivery(),
            'card' => ['holder' => 'Jane Buyer', 'last4' => '4242', 'expiry' => '12/29'],
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $market->refresh();
        $this->assertSame(52.0, (float) $market->available_quantity);

        // Cart is now empty (the ordered item flipped to status=ordered).
        $this->actingAs($buyer)->get(route('checkout.index'))
            ->assertInertia(fn ($page) => $page->where('items', []));
    }

    public function test_ordering_more_than_available_quantity_is_rejected(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $market = Market::query()->create([
            'user_id' => $seller->id,
            'title' => 'Small Lot',
            'quantity' => 300,
            'available_quantity' => 5,
            'unit' => 'kg',
            'price_per_unit' => 5,
            'status' => 'live',
        ]);

        app(CartService::class)->addItem($buyer->id, 'market', $market->id, 20);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(CheckoutService::class)->placeOrder($buyer, 'card', $this->validDelivery(), [
            'holder' => 'Jane Buyer',
            'last4' => '4242',
            'expiry' => '12/29',
        ]);
    }
}
