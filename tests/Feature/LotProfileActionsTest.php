<?php

namespace Tests\Feature;

use App\Models\CoffeeGrade;
use App\Models\Country;
use App\Models\CropVarietyMetadata;
use App\Models\Lot;
use App\Models\LotImage;
use App\Models\Market;
use App\Models\ProcessingMetadata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LotProfileActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_creator_can_update_their_lot(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $response = $this->actingAs($creator)->patch(route('lot.update', $lot), $this->validPayload([
            'lot_name' => 'Updated Reserve',
            'notes' => 'Updated after re-grading.',
            'currency' => 'KES',
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('lots', [
            'id' => $lot->id,
            'lot_name' => 'Updated Reserve',
            'notes' => 'Updated after re-grading.',
            'currency' => 'KES',
        ]);
    }

    public function test_a_non_creator_cannot_update_the_lot(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($otherUser)
            ->patch(route('lot.update', $lot), $this->validPayload())
            ->assertForbidden();
    }

    public function test_the_creator_can_delete_their_lot(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $response = $this->actingAs($creator)->delete(route('lot.destroy', $lot));

        $response->assertRedirect(route('store.show'));
        $this->assertDatabaseMissing('lots', ['id' => $lot->id]);
    }

    public function test_a_non_creator_cannot_delete_the_lot(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($otherUser)
            ->delete(route('lot.destroy', $lot))
            ->assertForbidden();

        $this->assertDatabaseHas('lots', ['id' => $lot->id]);
    }

    public function test_publishing_requires_title_quantity_and_price(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $response = $this->actingAs($creator)->post(route('lot.publish', $lot), []);

        $response->assertSessionHasErrors(['title', 'quantity', 'price_per_unit']);
        $this->assertDatabaseMissing('markets', ['lot_id' => $lot->id]);
    }

    public function test_publishing_creates_a_market_listing_from_the_form(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $response = $this->actingAs($creator)->post(
            route('lot.publish', $lot),
            $this->validPublishPayload([
                'title' => 'Bugisu AA Premium',
                'quantity' => 500,
                'price_per_unit' => 7.25,
                'currency' => 'KES',
                'delivery_location' => 'Kampala Warehouse',
            ]),
        );

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('markets', [
            'lot_id' => $lot->id,
            'status' => 'live',
            'title' => 'Bugisu AA Premium',
            'quantity' => 500,
            'price_per_unit' => 7.25,
            'currency' => 'KES',
            'delivery_location' => 'Kampala Warehouse',
        ]);
    }

    public function test_available_quantity_defaults_to_quantity_when_omitted(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($creator)->post(
            route('lot.publish', $lot),
            $this->validPublishPayload(['quantity' => 400]),
        )->assertSessionHasNoErrors();

        $this->assertDatabaseHas('markets', ['lot_id' => $lot->id, 'quantity' => 400, 'available_quantity' => 400]);
    }

    public function test_publishing_an_already_published_lot_returns_an_error_without_duplicating(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($creator)->post(route('lot.publish', $lot), $this->validPublishPayload());
        $this->actingAs($creator)->post(route('lot.publish', $lot), $this->validPublishPayload());

        $this->assertSame(1, Market::where('lot_id', $lot->id)->count());
    }

    public function test_lot_profile_reports_whether_the_lot_is_published(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($creator)->get(route('lot.show', $lot))
            ->assertInertia(fn ($page) => $page->where('lot.is_published', false));

        $this->actingAs($creator)->post(route('lot.publish', $lot), $this->validPublishPayload());

        $this->actingAs($creator)->get(route('lot.show', $lot))
            ->assertInertia(fn ($page) => $page->where('lot.is_published', true));
    }

    public function test_the_creator_can_unpublish_their_lot(): void
    {
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($creator)->post(route('lot.publish', $lot), $this->validPublishPayload());
        $this->assertDatabaseHas('markets', ['lot_id' => $lot->id]);

        $this->actingAs($creator)->delete(route('lot.unpublish', $lot))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('markets', ['lot_id' => $lot->id]);
    }

    public function test_a_non_creator_cannot_unpublish_the_lot(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($creator)->post(route('lot.publish', $lot), $this->validPublishPayload());

        $this->actingAs($otherUser)
            ->delete(route('lot.unpublish', $lot))
            ->assertForbidden();

        $this->assertDatabaseHas('markets', ['lot_id' => $lot->id]);
    }

    public function test_the_creator_can_upload_up_to_three_lot_images(): void
    {
        Storage::fake('public');
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($creator)->post(route('lot.images.store', $lot), [
            'images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
                UploadedFile::fake()->image('c.jpg'),
                UploadedFile::fake()->image('d.jpg'),
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(3, LotImage::where('lot_id', $lot->id)->count());
    }

    public function test_the_creator_can_delete_a_lot_image(): void
    {
        Storage::fake('public');
        $creator = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($creator)->post(route('lot.images.store', $lot), [
            'images' => [UploadedFile::fake()->image('a.jpg')],
        ]);
        $image = LotImage::where('lot_id', $lot->id)->firstOrFail();

        $this->actingAs($creator)->delete(route('lot.images.destroy', [$lot, $image]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('lot_images', ['id' => $image->id]);
    }

    public function test_a_non_creator_cannot_upload_lot_images(): void
    {
        Storage::fake('public');
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $lot = $this->makeLot($creator);

        $this->actingAs($otherUser)->post(route('lot.images.store', $lot), [
            'images' => [UploadedFile::fake()->image('a.jpg')],
        ])->assertForbidden();
    }

    /**
     * A full, valid lot.publish payload. Individual fields can be
     * overridden to trigger a specific failure or check a specific value.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPublishPayload(array $overrides = []): array
    {
        return [
            'title' => 'Bugisu AA',
            'quantity' => 600,
            'price_per_unit' => 5.5,
            ...$overrides,
        ];
    }

    /**
     * A full, valid lot.update payload, seeding whatever metadata it
     * needs. Individual fields can be overridden.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        ProcessingMetadata::query()->firstOrCreate(['slug' => 'washed'], ['name' => 'Washed', 'is_active' => true]);
        CoffeeGrade::query()->firstOrCreate(['slug' => 'a1'], ['name' => 'A1', 'is_active' => true]);
        CropVarietyMetadata::query()->firstOrCreate(['slug' => 'arabica'], ['name' => 'Arabica', 'is_active' => true]);
        Country::query()->firstOrCreate(['iso2' => 'ET'], ['name' => 'Ethiopia', 'iso3' => 'ETH', 'is_coffee_producer' => true]);

        return [
            'process' => 'Washed',
            'grade' => 'A1',
            'variety' => 'Arabica',
            'origin' => 'Ethiopia',
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
     * Create a persisted lot with the minimum required attributes.
     */
    private function makeLot(User $user): Lot
    {
        return Lot::query()->create([
            'user_id' => $user->id,
            'lot_number' => 'LOT-TEST-'.strtoupper(Str::random(6)),
            'process' => 'Washed',
            'grade' => 'A1',
            'quantity_bags' => 10,
            'bag_weight_kg' => 60,
            'net_weight_kg' => 600,
            'price' => 5.5,
        ]);
    }
}
