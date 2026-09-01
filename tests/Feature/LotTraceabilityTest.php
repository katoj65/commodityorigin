<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\User;
use App\Services\BlockchainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LotTraceabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_traceability_page_exposes_the_lots_provenance_fields(): void
    {
        $user = User::factory()->create();
        $lot = $this->makeLot($user, [
            'variety' => 'Arabica',
            'origin' => 'Ethiopia',
            'region' => 'Sidama',
            'altitude' => 1900,
            'year_of_harvest' => 2026,
            'moisture' => 11.5,
            'defects_percentage' => 2.5,
            'screen' => '16/18',
            'currency' => 'USD',
            'acidity' => 8.5,
            'body' => 8.0,
            'flavor' => 8.25,
            'aroma' => 8.5,
            'balance' => 8.0,
            'aftertaste' => 7.75,
        ]);

        $this->actingAs($user)
            ->get(route('lot.traceability', $lot))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lot/LotTraceability')
                ->where('lot.variety', 'Arabica')
                ->where('lot.origin', 'Ethiopia')
                ->where('lot.region', 'Sidama')
                ->where('lot.altitude', 1900)
                ->where('lot.year_of_harvest', 2026)
                ->where('lot.moisture', 11.5)
                ->where('lot.defects_percentage', 2.5)
                ->where('lot.screen', '16/18')
                ->where('lot.currency', 'USD')
                ->where('lot.acidity', 8.5)
                ->where('lot.body', 8)
                ->where('lot.flavor', 8.25)
                ->where('lot.aroma', 8.5)
                ->where('lot.balance', 8)
                ->where('lot.aftertaste', 7.75)
                ->where('blockchain', null)
            );
    }

    public function test_the_traceability_page_exposes_blockchain_data_and_timeline_entry_once_committed(): void
    {
        $user = User::factory()->create();
        $lot = $this->makeLot($user);

        $block = app(BlockchainService::class)->commitLot($lot, $user->id);

        $response = $this->actingAs($user)->get(route('lot.traceability', $lot));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Lot/LotTraceability')
            ->where('blockchain.block_number', $block->block_number)
            ->where('blockchain.hash', $block->hash)
        );

        $timeline = $response->viewData('page')['props']['timeline'];

        $this->assertTrue(collect($timeline)->contains(fn ($entry) => $entry['stage'] === 'blockchain'));
    }

    /**
     * Create a persisted lot with the minimum required attributes.
     *
     * @param  array<string, mixed>  $overrides
     */
    private function makeLot(User $user, array $overrides = []): Lot
    {
        return Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
            ...$overrides,
        ]);
    }
}
