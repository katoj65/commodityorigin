<?php

namespace Tests\Feature;

use App\Models\FlavorMetadata;
use Database\Seeders\FlavorMetadataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlavorMetadataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_an_active_entry_for_every_flavor(): void
    {
        (new FlavorMetadataSeeder())->run();

        $expectedNames = [
            'Chocolate', 'Cocoa', 'Caramel', 'Honey', 'Brown Sugar', 'Vanilla', 'Toffee', 'Molasses',
            'Almond', 'Hazelnut', 'Cashew', 'Peanut',
            'Blueberry', 'Strawberry', 'Blackcurrant', 'Cherry', 'Apple', 'Peach', 'Apricot', 'Orange', 'Lemon', 'Mango',
            'Tropical Fruit', 'Raisin', 'Dried Fruit',
            'Jasmine', 'Rose',
            'Cinnamon', 'Clove',
            'Black Tea', 'Green Tea', 'Wine', 'Tobacco',
            'Citrus', 'Berry', 'Nutty', 'Floral', 'Herbal', 'Spicy',
        ];

        foreach ($expectedNames as $name) {
            $this->assertDatabaseHas('flavor_metadata', ['name' => $name, 'is_active' => true]);
        }

        $this->assertSame(count($expectedNames), FlavorMetadata::query()->count());
    }

    public function test_seeding_is_idempotent(): void
    {
        $seeder = new FlavorMetadataSeeder();
        $seeder->run();
        $seeder->run();

        $this->assertSame(39, FlavorMetadata::query()->count());
    }
}
