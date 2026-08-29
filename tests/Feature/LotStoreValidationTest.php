<?php

namespace Tests\Feature;

use App\Models\CoffeeGrade;
use App\Models\Country;
use App\Models\CropVarietyMetadata;
use App\Models\ProcessingMetadata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotStoreValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_new_lot_forms_provenance_fields_are_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('lot.store'), [
            'process' => $this->processingMethod(),
            'grade' => $this->coffeeGrade(),
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ]);

        $response->assertSessionHasErrors([
            'variety', 'origin', 'region', 'year_of_harvest', 'moisture', 'screen',
        ]);
        $this->assertDatabaseCount('lots', 0);
    }

    public function test_variety_must_be_an_active_crop_variety(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('lot.store'), $this->validPayload([
            'variety' => 'Not A Real Variety',
        ]));

        $response->assertSessionHasErrors(['variety']);
        $this->assertDatabaseCount('lots', 0);
    }

    public function test_origin_must_be_a_coffee_producing_country(): void
    {
        $user = User::factory()->create();

        Country::query()->create([
            'name' => 'Iceland',
            'iso2' => 'IS',
            'iso3' => 'ISL',
            'is_coffee_producer' => false,
        ]);

        $response = $this->actingAs($user)->post(route('lot.store'), $this->validPayload([
            'origin' => 'Iceland',
        ]));

        $response->assertSessionHasErrors(['origin']);
        $this->assertDatabaseCount('lots', 0);
    }

    public function test_a_lot_is_created_when_every_provenance_field_is_valid(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('lot.store'), $this->validPayload(['altitude' => 1900]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lots', [
            'variety' => 'Arabica',
            'origin' => 'Ethiopia',
            'region' => 'Sidama',
            'altitude' => 1900,
            'year_of_harvest' => 2026,
            'screen' => '16/18',
        ]);
    }

    public function test_altitude_is_optional_and_persists_when_given(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('lot.store'), $this->validPayload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lots', ['altitude' => null]);
    }

    /**
     * A full, valid lot.store payload, seeding whatever metadata it needs.
     * Individual fields can be overridden to trigger a specific failure.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
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
            ...$overrides,
        ];
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
