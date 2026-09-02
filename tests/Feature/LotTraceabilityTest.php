<?php

namespace Tests\Feature;

use App\Models\AcidityMetadata;
use App\Models\AftertasteMetadata;
use App\Models\AromaMetadata;
use App\Models\BodyMetadata;
use App\Models\FlavorMetadata;
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
        FlavorMetadata::query()->create(['slug' => 'chocolate', 'name' => 'Chocolate', 'sort_order' => 1, 'is_active' => true]);
        BodyMetadata::query()->create(['slug' => 'medium', 'name' => 'Medium', 'sort_order' => 1, 'is_active' => true]);
        AcidityMetadata::query()->create(['slug' => 'citrus', 'name' => 'Citrus', 'sort_order' => 1, 'is_active' => true]);
        AftertasteMetadata::query()->create(['slug' => 'clean', 'name' => 'Clean', 'sort_order' => 1, 'is_active' => true]);
        AromaMetadata::query()->create(['slug' => 'floral', 'name' => 'Floral', 'sort_order' => 1, 'is_active' => true]);

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
            'acidity' => 'citrus',
            'body' => 'medium',
            'flavor' => 'chocolate',
            'aroma' => 'floral',
            'balance' => 8.0,
            'aftertaste' => 'clean',
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
                ->where('lot.acidity', 'Citrus')
                ->where('lot.body', 'Medium')
                ->where('lot.flavor', 'Chocolate')
                ->where('lot.aroma', 'Floral')
                ->where('lot.balance', 8)
                ->where('lot.aftertaste', 'Clean')
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
