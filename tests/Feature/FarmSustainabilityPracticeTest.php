<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\FarmSustainabilityPractice;
use App\Models\SustainabilityPracticesMetadata;
use App\Models\User;
use Database\Seeders\SustainabilityPracticesMetadataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmSustainabilityPracticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_creator_can_record_a_practice_via_the_http_endpoint(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator);
        (new SustainabilityPracticesMetadataSeeder())->run();

        $response = $this->actingAs($creator)->post(route('farm.sustainability-practices.store', $farm), [
            'practice' => 'shade_grown',
            'description' => 'Canopy cover maintained above 40%.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('farm_sustainability_practices', [
            'farm_id' => $farm->id,
            'user_id' => $creator->id,
            'practice' => 'shade_grown',
            'description' => 'Canopy cover maintained above 40%.',
        ]);
    }

    public function test_a_non_creator_cannot_record_a_practice(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $farm = $this->makeFarm($creator);
        (new SustainabilityPracticesMetadataSeeder())->run();

        $response = $this->actingAs($stranger)->post(route('farm.sustainability-practices.store', $farm), [
            'practice' => 'shade_grown',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('farm_sustainability_practices', ['farm_id' => $farm->id]);
    }

    public function test_the_practice_must_be_an_active_metadata_slug(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator);
        SustainabilityPracticesMetadata::query()->create([
            'slug' => 'retired_practice',
            'name' => 'Retired Practice',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $response = $this->actingAs($creator)->post(route('farm.sustainability-practices.store', $farm), [
            'practice' => 'retired_practice',
        ]);
        $response->assertSessionHasErrors('practice');

        $response = $this->actingAs($creator)->post(route('farm.sustainability-practices.store', $farm), [
            'practice' => 'not-a-real-slug',
        ]);
        $response->assertSessionHasErrors('practice');
    }

    public function test_the_farm_profile_page_exposes_practices_most_recent_first_and_the_option_list(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator);
        (new SustainabilityPracticesMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('farm.sustainability-practices.store', $farm), ['practice' => 'intercropping']);
        $this->actingAs($creator)->post(route('farm.sustainability-practices.store', $farm), ['practice' => 'agroforestry', 'description' => 'Native trees planted along the boundary.']);

        $this->actingAs($creator)->get(route('farm.show', $farm))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Farm/FarmProfile')
                ->has('sustainabilityPractices', 2)
                ->where('sustainabilityPractices.0.practice', 'agroforestry')
                ->where('sustainabilityPractices.0.description', 'Native trees planted along the boundary.')
                ->where('sustainabilityPractices.0.recorded_by.name', $creator->name)
                ->where('sustainabilityPractices.1.practice', 'intercropping')
                ->has('sustainabilityPracticeOptions', 10)
            );
    }

    public function test_the_creator_can_delete_a_practice(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator);
        (new SustainabilityPracticesMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('farm.sustainability-practices.store', $farm), ['practice' => 'intercropping']);
        $practice = FarmSustainabilityPractice::query()->where('farm_id', $farm->id)->firstOrFail();

        $response = $this->actingAs($creator)->delete(route('farm.sustainability-practices.destroy', [$farm, $practice]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseMissing('farm_sustainability_practices', ['id' => $practice->id]);
    }

    public function test_a_non_creator_cannot_delete_a_practice(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $farm = $this->makeFarm($creator);
        (new SustainabilityPracticesMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('farm.sustainability-practices.store', $farm), ['practice' => 'intercropping']);
        $practice = FarmSustainabilityPractice::query()->where('farm_id', $farm->id)->firstOrFail();

        $response = $this->actingAs($stranger)->delete(route('farm.sustainability-practices.destroy', [$farm, $practice]));

        $response->assertForbidden();
        $this->assertDatabaseHas('farm_sustainability_practices', ['id' => $practice->id]);
    }

    public function test_a_practice_belonging_to_a_different_farm_cannot_be_deleted_through_this_farm(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator);
        $otherFarm = $this->makeFarm($creator);
        (new SustainabilityPracticesMetadataSeeder())->run();

        $this->actingAs($creator)->post(route('farm.sustainability-practices.store', $otherFarm), ['practice' => 'intercropping']);
        $practice = FarmSustainabilityPractice::query()->where('farm_id', $otherFarm->id)->firstOrFail();

        $response = $this->actingAs($creator)->delete(route('farm.sustainability-practices.destroy', [$farm, $practice]));

        $response->assertNotFound();
        $this->assertDatabaseHas('farm_sustainability_practices', ['id' => $practice->id]);
    }

    private function makeFarm(User $user): Farm
    {
        return Farm::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Farm',
        ]);
    }
}
