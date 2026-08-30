<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\Market;
use App\Models\User;
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
