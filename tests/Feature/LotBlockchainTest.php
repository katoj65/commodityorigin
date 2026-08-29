<?php

namespace Tests\Feature;

use App\Models\Blockchain;
use App\Models\CoffeeGrade;
use App\Models\Country;
use App\Models\CropVarietyMetadata;
use App\Models\Lot;
use App\Models\ProcessingMetadata;
use App\Models\User;
use App\Services\BlockchainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotBlockchainTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_block_is_committed_when_a_lot_is_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('lot.store'), [
            'process' => $this->processingMethod(),
            'grade' => $this->coffeeGrade(),
            'variety' => $this->variety(),
            'origin' => $this->originCountry(),
            'region' => 'Sidama',
            'year_of_harvest' => 2026,
            'moisture' => 11.5,
            'screen' => '16/18',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ])->assertSessionHasNoErrors();

        $lot = Lot::query()->firstOrFail();

        $this->assertNotNull($lot->blockchain);
        $this->assertSame($lot->id, $lot->blockchain->lot_id);
        $this->assertSame($user->id, $lot->blockchain->user_id);
        $this->assertSame('confirmed', $lot->blockchain->status);

        $this->assertDatabaseHas('blockchains', [
            'lot_id' => $lot->id,
        ]);
    }

    public function test_block_numbers_and_previous_hashes_chain_across_lots(): void
    {
        $user = User::factory()->create();
        $process = $this->processingMethod();
        $grade = $this->coffeeGrade();
        $variety = $this->variety();
        $origin = $this->originCountry();
        $common = [
            'process' => $process,
            'grade' => $grade,
            'variety' => $variety,
            'origin' => $origin,
            'region' => 'Sidama',
            'year_of_harvest' => 2026,
            'moisture' => 11.5,
            'screen' => '16/18',
            'bag_weight_kg' => 60,
        ];

        $this->actingAs($user)->post(route('lot.store'), [...$common, 'quantity_bags' => 5])
            ->assertSessionHasNoErrors();
        $this->actingAs($user)->post(route('lot.store'), [...$common, 'quantity_bags' => 6])
            ->assertSessionHasNoErrors();

        $blocks = Blockchain::query()->orderBy('block_number')->get();

        $this->assertCount(2, $blocks);
        $this->assertSame($blocks[0]->block_number + 1, $blocks[1]->block_number);
        $this->assertSame($blocks[0]->hash, $blocks[1]->previous_hash);
    }

    public function test_committing_an_already_committed_lot_is_idempotent(): void
    {
        $lot = Lot::query()->create([
            'user_id' => User::factory()->create()->id,
            'lot_number' => 'LOT-TEST-BLOCK',
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ]);

        $service = app(BlockchainService::class);

        $first = $service->commitLot($lot);
        $second = $service->commitLot($lot);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Blockchain::query()->where('lot_id', $lot->id)->count());
    }

    /**
     * Seed an active processing method and return its name.
     */
    private function processingMethod(): string
    {
        ProcessingMetadata::query()->create([
            'slug' => 'washed',
            'name' => 'Washed',
            'is_active' => true,
        ]);

        return 'Washed';
    }

    /**
     * Seed an active coffee grade and return its name.
     */
    private function coffeeGrade(): string
    {
        CoffeeGrade::query()->create([
            'slug' => 'a1',
            'name' => 'A1',
            'is_active' => true,
        ]);

        return 'A1';
    }

    /**
     * Seed an active crop variety and return its name.
     */
    private function variety(): string
    {
        CropVarietyMetadata::query()->create([
            'slug' => 'arabica',
            'name' => 'Arabica',
            'is_active' => true,
        ]);

        return 'Arabica';
    }

    /**
     * Seed a coffee-producing country and return its name.
     */
    private function originCountry(): string
    {
        Country::query()->create([
            'name' => 'Ethiopia',
            'iso2' => 'ET',
            'iso3' => 'ETH',
            'is_coffee_producer' => true,
        ]);

        return 'Ethiopia';
    }
}
