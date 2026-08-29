<?php

namespace Tests\Feature;

use App\Helpers\QrCodeHelper;
use App\Models\CoffeeGrade;
use App\Models\Country;
use App\Models\CropVarietyMetadata;
use App\Models\Lot;
use App\Models\ProcessingMetadata;
use App\Models\User;
use App\Services\LotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LotQrCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_code_is_generated_and_stored_when_a_lot_is_created(): void
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

        $this->assertNotNull($lot->qr_code);
        $this->assertStringContainsString('<svg', $lot->qr_code);
    }

    public function test_lot_service_stores_the_qr_code_link_on_the_lot_when_creating(): void
    {
        $user = User::factory()->create();

        $lot = app(LotService::class)->create([
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 8,
            'bag_weight_kg' => 60,
        ], null, $user->id);

        // The returned model and the persisted row both carry the QR code.
        $this->assertStringContainsString('<svg', (string) $lot->qr_code);
        $this->assertStringContainsString('<svg', (string) $lot->fresh()->qr_code);
        $this->assertDatabaseHas('lots', [
            'id' => $lot->id,
            'qr_code' => $lot->qr_code,
        ]);
    }

    public function test_qr_code_encodes_the_lots_traceability_url(): void
    {
        $lot = $this->makeLot();

        $this->assertSame(route('lot.traceability', $lot), QrCodeHelper::lotUrl($lot));
        $this->assertStringContainsString("/lot/{$lot->id}/traceability", QrCodeHelper::lotUrl($lot));
        $this->assertStringContainsString('<svg', (string) $lot->fresh()->qr_code);
    }

    public function test_qr_code_is_backfilled_when_viewing_a_lot_without_one(): void
    {
        $lot = $this->makeLot();
        $lot->forceFill(['qr_code' => null])->saveQuietly();

        $this->actingAs($lot->user)->get(route('lot.show', $lot))->assertOk();

        $this->assertStringContainsString('<svg', (string) $lot->fresh()->qr_code);
    }

    public function test_helper_generates_svg_markup_for_arbitrary_data(): void
    {
        $svg = QrCodeHelper::generate('https://example.com/trace/1');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('viewBox', $svg);
    }

    /**
     * Create a persisted lot with the minimum required attributes.
     */
    private function makeLot(): Lot
    {
        $user = User::factory()->create();

        return Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
        ]);
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
