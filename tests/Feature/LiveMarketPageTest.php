<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LiveMarketPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('market.live'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_see_real_data_backed_props(): void
    {
        $user = User::factory()->create();

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

        $response = $this->actingAs($user)->get(route('market.live'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Market/LiveMarket')
            ->has('lots', 1)
            ->where('lots.0.name', 'Kapchorwa AA')
            ->where('analysis.total_listings', 1)
            ->has('demand.tiers')
            ->has('opportunities')
        );
    }
}
