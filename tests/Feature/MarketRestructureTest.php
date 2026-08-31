<?php

namespace Tests\Feature;

use App\Http\Resources\CartItemResource;
use App\Models\Batch;
use App\Models\BatchFarmCollection;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\FarmCollection;
use App\Models\Lot;
use App\Models\LotBatch;
use App\Models\Market;
use App\Models\User;
use App\Services\CartService;
use App\Services\MarketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketRestructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_metadata_backed_accessors_read_through_to_the_metadata_column(): void
    {
        $market = Market::query()->create([
            'title' => 'Yirgacheffe Reserve',
            'quantity' => 500,
            'price_per_unit' => 6.5,
            'metadata' => [
                'origin' => 'Ethiopia',
                'type' => 'Arabica',
                'process' => 'Washed',
                'quality_score' => 88.5,
                'demand' => 'high',
                'badges' => ['Organic', 'Fair Trade'],
                'target_market' => 'EU',
                'image' => 'markets/cover.jpg',
            ],
        ]);

        $this->assertSame('Ethiopia', $market->origin);
        $this->assertSame('Arabica', $market->type);
        $this->assertSame('Washed', $market->process);
        $this->assertSame(88.5, $market->quality_score);
        $this->assertSame('high', $market->demand);
        $this->assertSame(['Organic', 'Fair Trade'], $market->badges);
        $this->assertSame('EU', $market->target_market);
        $this->assertSame('markets/cover.jpg', $market->image);
    }

    public function test_lot_code_accessor_reads_the_linked_lots_number(): void
    {
        $user = User::factory()->create();
        $lot = Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ]);
        $market = Market::query()->create([
            'lot_id' => $lot->id,
            'title' => 'Test Listing',
            'quantity' => 100,
            'price_per_unit' => 5,
        ]);

        $this->assertSame($lot->lot_number, $market->lot_code);
    }

    public function test_image_accessor_prefers_the_lots_own_photo_over_the_metadata_placeholder(): void
    {
        $user = User::factory()->create();

        // A lot with its own cover photo — the market's image must be
        // that real photo, not the metadata placeholder.
        $lotWithCover = Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
            'image' => 'lot-images/cover.jpg',
        ]);
        $marketWithCover = Market::query()->create([
            'lot_id' => $lotWithCover->id,
            'title' => 'Real Photo Listing',
            'quantity' => 100,
            'price_per_unit' => 5,
            'metadata' => ['image' => 'markets/placeholder.jpg'],
        ]);
        $this->assertSame('lot-images/cover.jpg', $marketWithCover->image);

        // A lot with no cover but a gallery photo — falls back to the
        // first gallery photo, still real, still not the placeholder.
        $lotWithGallery = Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ]);
        $lotWithGallery->images()->create(['image' => 'lot-images/gallery-1.jpg', 'position' => 0]);
        $marketWithGallery = Market::query()->create([
            'lot_id' => $lotWithGallery->id,
            'title' => 'Gallery Photo Listing',
            'quantity' => 100,
            'price_per_unit' => 5,
            'metadata' => ['image' => 'markets/placeholder.jpg'],
        ]);
        $this->assertSame('lot-images/gallery-1.jpg', $marketWithGallery->image);

        // A lot with no photos of its own — falls back to the metadata
        // placeholder, same as before.
        $lotWithNoPhoto = Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ]);
        $marketWithNoPhoto = Market::query()->create([
            'lot_id' => $lotWithNoPhoto->id,
            'title' => 'Placeholder Listing',
            'quantity' => 100,
            'price_per_unit' => 5,
            'metadata' => ['image' => 'markets/placeholder.jpg'],
        ]);
        $this->assertSame('markets/placeholder.jpg', $marketWithNoPhoto->image);

        // The market grid / filtered results shape their output the same
        // way, and both eager-load lot.images to avoid N+1s.
        $gridImages = collect(app(MarketService::class)->marketPageListing())->pluck('image', 'name');
        $this->assertSame('lot-images/cover.jpg', $gridImages['Real Photo Listing']);
        $this->assertSame('lot-images/gallery-1.jpg', $gridImages['Gallery Photo Listing']);
        $this->assertSame('markets/placeholder.jpg', $gridImages['Placeholder Listing']);
    }

    public function test_publishing_a_lot_creates_a_market_with_the_new_schema(): void
    {
        $creator = User::factory()->create();
        $lot = Lot::query()->create([
            'user_id' => $creator->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'lot_name' => 'Bugisu AA',
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
            'net_weight_kg' => 600,
            'price' => 5.5,
        ]);

        $this->actingAs($creator)->post(route('lot.publish', $lot), [
            'title' => $lot->lot_name,
            'quantity' => $lot->net_weight_kg,
            'price_per_unit' => $lot->price,
        ])->assertSessionHasNoErrors();

        $market = Market::where('lot_id', $lot->id)->firstOrFail();

        $this->assertSame('Bugisu AA', $market->title);
        $this->assertSame('600.00', (string) $market->quantity);
        $this->assertSame('600.00', (string) $market->available_quantity);
        $this->assertSame('kg', $market->unit);
        $this->assertSame('USD', $market->currency);
        $this->assertSame('5.50', (string) $market->price_per_unit);
        $this->assertSame('fixed', $market->pricing_type);
        $this->assertTrue($market->is_public);
        $this->assertFalse($market->is_featured);
        $this->assertSame('Washed', $market->process);
    }

    public function test_updating_a_listing_merges_into_metadata_without_clobbering_other_keys(): void
    {
        $owner = User::factory()->create();
        $market = Market::query()->create([
            'user_id' => $owner->id,
            'title' => 'Original Title',
            'quantity' => 200,
            'price_per_unit' => 4,
            'metadata' => ['demand' => 'high', 'badges' => ['Organic']],
        ]);

        $this->actingAs($owner)->patch(route('market.update', $market), [
            'name' => 'Updated Title',
            'origin' => 'Kenya',
            'type' => 'Arabica',
            'process' => 'Natural',
            'price_per_kg' => 7.25,
            'quantity' => 250,
            'notes' => 'Updated description',
        ])->assertSessionHasNoErrors();

        $market->refresh();

        $this->assertSame('Updated Title', $market->title);
        $this->assertSame('Updated description', $market->description);
        $this->assertSame('7.25', (string) $market->price_per_unit);
        $this->assertSame('250.00', (string) $market->quantity);
        $this->assertSame('Kenya', $market->origin);
        $this->assertSame('Arabica', $market->type);
        $this->assertSame('Natural', $market->process);
        // Pre-existing metadata keys not touched by the edit form survive.
        $this->assertSame('high', $market->demand);
        $this->assertSame(['Organic'], $market->badges);
    }

    public function test_market_page_listing_shapes_output_with_backward_compatible_keys(): void
    {
        Market::query()->create([
            'title' => 'Featured Lot',
            'quantity' => 300,
            'price_per_unit' => 8,
            'status' => 'live',
            'is_featured' => true,
            'metadata' => ['origin' => 'Uganda', 'type' => 'Robusta'],
        ]);

        $service = app(MarketService::class);
        $listing = $service->marketPageListing();

        $this->assertCount(1, $listing);
        $this->assertSame('Featured Lot', $listing[0]['name']);
        $this->assertSame(8.0, $listing[0]['price_per_kg']);
        $this->assertSame('Uganda', $listing[0]['origin']);
        $this->assertSame('Robusta', $listing[0]['type']);
        $this->assertTrue($listing[0]['is_featured']);
    }

    public function test_show_joins_the_farm_chain_for_a_lot_backed_listing(): void
    {
        $user = User::factory()->create();

        $farm = Farm::query()->create([
            'user_id' => $user->id,
            'name' => 'Sipi Falls Farm',
            'district' => 'Kapchorwa',
            'region' => 'Eastern',
            'country' => 'Uganda',
            'latitude' => 1.4021,
            'longitude' => 34.4694,
        ]);
        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'farmer_number' => 'FRM-TEST-01',
            'first_name' => 'Grace',
            'last_name' => 'Chebet',
            'tel' => '+256700000000',
            'district' => 'Kapchorwa',
        ]);
        $farm->farmers()->attach($farmer->id, ['farm_code' => $farm->farm_code, 'status' => 'active']);

        $collection = FarmCollection::query()->create([
            'user_id' => $user->id,
            'farm_id' => $farm->id,
            'collection_code' => 'COL-TEST-01',
            'status' => 'verified',
            'collection_date' => '2026-06-01',
            'harvest_season' => 'June - August',
            'coffee_type' => 'Arabica',
            'quantity' => 500,
        ]);

        $batch = Batch::query()->create([
            'user_id' => $user->id,
            'batch_number' => 'BTC-TEST-01',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 10,
            'weight' => 600,
            'processing_date' => '2026-06-10',
        ]);
        BatchFarmCollection::query()->create([
            'batch_id' => $batch->id,
            'farm_collection_id' => $collection->id,
            'farm_collection_code' => $collection->collection_code,
            'user_id' => $user->id,
        ]);

        $lot = Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ]);
        LotBatch::query()->create([
            'lot_id' => $lot->id,
            'batch_id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'allocation_kg' => 600,
            'user_id' => $user->id,
        ]);

        $market = Market::query()->create([
            'lot_id' => $lot->id,
            'user_id' => $user->id,
            'title' => 'Sipi Falls AA',
            'quantity' => 500,
            'price_per_unit' => 6,
            'status' => 'live',
        ]);

        $result = app(MarketService::class)->show($market);

        $this->assertSame('Sipi Falls Farm', $result['farm']['name']);
        $this->assertSame('Kapchorwa', $result['farm']['district']);
        $this->assertSame(1, $result['farmer_count']);
        $this->assertSame('June - August', $result['harvest_season']);
        $this->assertNotNull($result['supply_chain']);
        $this->assertTrue(collect($result['supply_chain'])->contains(fn ($step) => $step['label'] === 'Harvest'));
        $this->assertTrue(collect($result['supply_chain'])->contains(fn ($step) => $step['label'] === 'Processing'));
    }

    public function test_show_degrades_to_nulls_when_the_listing_has_no_lot(): void
    {
        $market = Market::query()->create([
            'title' => 'Standalone Listing',
            'quantity' => 200,
            'price_per_unit' => 4,
            'status' => 'live',
        ]);

        $result = app(MarketService::class)->show($market);

        $this->assertNull($result['farm']);
        $this->assertNull($result['farmer_count']);
        $this->assertNull($result['harvest_season']);
        $this->assertSame([], $result['lot_images']);
        $this->assertNull($result['lot_image']);
    }

    public function test_adding_a_listing_to_the_cart_from_its_product_page_is_reflected_back(): void
    {
        $buyer = User::factory()->create();
        $market = Market::query()->create([
            'title' => 'Cart Test Listing',
            'quantity' => 200,
            'price_per_unit' => 6,
            'status' => 'live',
        ]);

        $this->actingAs($buyer)
            ->get(route('market.show', $market))
            ->assertInertia(fn ($page) => $page->where('item.id', $market->id));

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $buyer->id,
            'cartable_id' => $market->id,
        ]);

        // The product page's "Add to Cart" button always sends quantity 1,
        // matching the market listings grid — clicking it twice
        // accumulates rather than replacing the quantity.
        $this->actingAs($buyer)->post(route('checkout.items.store'), [
            'cartable_type' => 'market',
            'cartable_id' => $market->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $this->actingAs($buyer)->post(route('checkout.items.store'), [
            'cartable_type' => 'market',
            'cartable_id' => $market->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $buyer->id,
            'cartable_id' => $market->id,
            'cartable_type' => Market::class,
            'quantity' => 2,
        ]);
    }

    public function test_adding_a_listing_to_the_cart_locks_in_its_real_price_and_name(): void
    {
        $buyer = User::factory()->create();
        $market = Market::query()->create([
            'title' => 'Bugisu AA Premium',
            'quantity' => 300,
            'available_quantity' => 250,
            'unit' => 'kg',
            'price_per_unit' => 7.25,
            'status' => 'live',
        ]);

        $item = app(CartService::class)->addItem($buyer->id, 'market', $market->id, 2);

        $this->assertSame('7.25', (string) $item->unit_price);

        $item->load('cartable');
        $resolved = CartItemResource::make($item)->resolve();

        $this->assertSame('Bugisu AA Premium', $resolved['name']);
        $this->assertSame(7.25, $resolved['current_price']);
        $this->assertSame(14.5, $resolved['line_total']);
        $this->assertSame('kg', $resolved['unit']);
        $this->assertSame(250.0, $resolved['available_quantity']);
    }

    public function test_filtered_listing_filters_by_metadata_backed_type_and_price(): void
    {
        Market::query()->create(['title' => 'A', 'quantity' => 100, 'price_per_unit' => 3, 'status' => 'live', 'metadata' => ['type' => 'arabica']]);
        Market::query()->create(['title' => 'B', 'quantity' => 100, 'price_per_unit' => 9, 'status' => 'live', 'metadata' => ['type' => 'robusta']]);

        $service = app(MarketService::class);

        $arabicaOnly = $service->filteredListing(['type' => 'arabica']);
        $this->assertCount(1, $arabicaOnly);
        $this->assertSame('A', $arabicaOnly->first()->title);

        $underFive = $service->filteredListing(['max_price' => 5]);
        $this->assertCount(1, $underFive);
        $this->assertSame('A', $underFive->first()->title);
    }
}
