<?php

namespace Tests\Feature;

use App\Models\Market;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExchangePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_is_public_and_shows_real_market_data(): void
    {
        Market::query()->create([
            'title' => 'Kapchorwa AA',
            'status' => 'live',
            'type' => 'Arabica',
            'origin' => 'Uganda',
            'quantity' => 500,
            'price_per_unit' => 12.5,
            'demand' => 'high',
            'quality_score' => 88,
        ]);

        $response = $this->get(route('exchange.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exchange/Index')
            ->where('analysis.total_listings', 1)
            ->has('demand.tiers')
        );
    }
}
