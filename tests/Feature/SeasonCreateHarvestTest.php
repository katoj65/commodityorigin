<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Farmer;
use App\Models\Harvest;
use App\Models\PickMethodMetadata;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonCreateHarvestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_owner_can_view_the_season_create_harvest_page(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $season = Season::query()->create([
            'user_id' => $user->id,
            'name' => 'Main Crop 2026',
            'region' => 'Mount Elgon',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('season.create-harvest', $season));

        $response->assertOk();
        $response->assertSee('Season/NewHarvestPage');
    }

    public function test_admin_owner_can_store_a_harvest_from_the_season_create_harvest_page(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        PickMethodMetadata::query()->create([
            'slug' => 'selective-picking',
            'name' => 'Selective Picking',
            'description' => null,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $season = Season::query()->create([
            'user_id' => $user->id,
            'name' => 'Main Crop 2026',
            'region' => 'Mount Elgon',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Samuel',
            'last_name' => 'Okello',
            'telephone' => '+256700000001',
            'email' => 'samuel@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Sipi Falls Union',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Sipi Falls Micro-Lot B',
            'location' => 'Mount Elgon',
            'size' => '6 acres',
            'altitude' => '1850m',
            'variety' => 'Arabica SL28',
            'latitude' => 1.2167,
            'longitude' => 34.4167,
        ]);

        $response = $this->actingAs($user)->post(route('season.store-harvest', $season), [
            'farm_id' => $farm->id,
            'variety' => 'Arabica SL28',
            'pick_method' => 'Selective Picking',
            'date_planted' => '2026-04-20',
            'harvest_date' => '2026-04-22',
            'harvest_season' => 'Main Crop',
            'price' => 4.85,
            'weight' => 1250,
            'ripeness_percentage' => 94,
            'foreign_matter_present' => false,
            'pest_damage' => false,
            'disease_signs' => false,
            'visible_defects' => false,
        ]);

        $response->assertRedirect(route('season.show', $season));

        $harvest = Harvest::query()->first();

        $this->assertNotNull($harvest);
        $this->assertSame($season->id, $harvest->season_id);
        $this->assertSame($farm->id, $harvest->farm_id);
        $this->assertSame('Main Crop', $harvest->harvest_season);
        $this->assertSame('active', $harvest->status);
    }

    public function test_admin_owner_can_view_the_season_create_batch_page(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $season = Season::query()->create([
            'user_id' => $user->id,
            'name' => 'Main Crop 2026',
            'region' => 'Mount Elgon',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('batch.create-season', $season));

        $response->assertOk();
        $response->assertSee('Batch/CreateBatch');
    }

    public function test_admin_owner_can_delete_a_harvest_from_the_season_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $season = Season::query()->create([
            'user_id' => $user->id,
            'name' => 'Main Crop 2026',
            'region' => 'Mount Elgon',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Samuel',
            'last_name' => 'Okello',
            'telephone' => '+256700000001',
            'email' => 'samuel@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Sipi Falls Union',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Sipi Falls Micro-Lot B',
            'location' => 'Mount Elgon',
            'size' => '6 acres',
            'altitude' => '1850m',
            'variety' => 'Arabica SL28',
            'latitude' => 1.2167,
            'longitude' => 34.4167,
        ]);

        $harvest = Harvest::query()->create([
            'user_id' => $user->id,
            'season_id' => $season->id,
            'farm_id' => $farm->id,
            'variety' => 'Arabica SL28',
            'date_planted' => '2026-04-20',
            'harvest_date' => '2026-04-22',
            'harvest_season' => 'Main Crop',
            'status' => 'active',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1250,
            'ripeness_percentage' => 94,
            'foreign_matter_present' => false,
            'pest_damage' => false,
            'disease_signs' => false,
            'visible_defects' => false,
        ]);

        $response = $this->actingAs($user)->delete(route('season.harvest.destroy', [$season, $harvest]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('harvests', [
            'id' => $harvest->id,
        ]);
    }
}
