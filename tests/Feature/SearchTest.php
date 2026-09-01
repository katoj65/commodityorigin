<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_searching_by_keyword_matches_the_listings_real_title(): void
    {
        $user = User::factory()->create();
        Market::query()->create([
            'user_id' => $user->id,
            'title' => 'Yirgacheffe Reserve',
            'quantity' => 100,
            'price_per_unit' => 6.5,
            'status' => 'live',
        ]);
        Market::query()->create([
            'user_id' => $user->id,
            'title' => 'Bugisu AA',
            'quantity' => 50,
            'price_per_unit' => 4,
            'status' => 'live',
        ]);

        $response = $this->actingAs($user)->get(route('search.index', ['q' => 'Yirgacheffe']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('results', 1)
            ->where('results.0.name', 'Yirgacheffe Reserve')
            ->where('results.0.price_per_kg', 6.5)
        );
    }

    public function test_searching_matches_metadata_backed_origin_type_and_process(): void
    {
        $user = User::factory()->create();
        Market::query()->create([
            'user_id' => $user->id,
            'title' => 'Reserve Lot',
            'quantity' => 100,
            'price_per_unit' => 6.5,
            'status' => 'live',
            'metadata' => ['origin' => 'Ethiopia', 'type' => 'Arabica', 'process' => 'Washed'],
        ]);

        $this->actingAs($user)->get(route('search.index', ['q' => 'Ethiopia']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('results', 1));

        $this->actingAs($user)->get(route('search.index', ['q' => 'Washed']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('results', 1));
    }

    public function test_searching_matches_a_lot_backed_listings_lot_number(): void
    {
        $user = User::factory()->create();
        $lot = Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-SEARCH-01',
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ]);
        Market::query()->create([
            'lot_id' => $lot->id,
            'user_id' => $user->id,
            'title' => 'Some Listing',
            'quantity' => 100,
            'price_per_unit' => 6.5,
            'status' => 'live',
        ]);

        $this->actingAs($user)->get(route('search.index', ['q' => 'LOT-SEARCH-01']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('results', 1)
                ->where('results.0.lot_code', 'LOT-SEARCH-01')
            );
    }

    public function test_price_and_type_filters_use_the_real_columns(): void
    {
        $user = User::factory()->create();
        Market::query()->create([
            'user_id' => $user->id,
            'title' => 'Cheap Robusta',
            'quantity' => 100,
            'price_per_unit' => 2,
            'status' => 'live',
            'metadata' => ['type' => 'Robusta'],
        ]);
        Market::query()->create([
            'user_id' => $user->id,
            'title' => 'Pricey Arabica',
            'quantity' => 100,
            'price_per_unit' => 20,
            'status' => 'live',
            'metadata' => ['type' => 'Arabica'],
        ]);

        $this->actingAs($user)->get(route('search.index', ['q' => 'coffee', 'min_price' => 10]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('results', 0));

        $this->actingAs($user)->get(route('search.index', ['q' => 'Arabica', 'type' => 'Arabica']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('results', 1)
                ->where('results.0.name', 'Pricey Arabica')
            );
    }

    public function test_suggest_endpoint_returns_the_real_title_and_price(): void
    {
        $user = User::factory()->create();
        Market::query()->create([
            'user_id' => $user->id,
            'title' => 'Suggest Me',
            'quantity' => 100,
            'price_per_unit' => 9.25,
            'status' => 'live',
        ]);

        $response = $this->actingAs($user)->getJson(route('search.suggest', ['q' => 'Suggest']));

        $response->assertOk();
        $response->assertJson([
            'results' => [
                ['name' => 'Suggest Me', 'price_per_kg' => 9.25],
            ],
        ]);
    }

    public function test_an_empty_keyword_returns_no_results_and_does_not_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('search.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('results', 0));

        $this->assertDatabaseCount('search_logs', 0);
    }
}
