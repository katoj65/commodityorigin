<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Farmer;
use App\Models\Harvest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HarvestStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_the_harvest_create_page(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('harvest.create'));

        $response->assertOk();
    }

    public function test_non_admin_users_cannot_view_the_harvest_create_page(): void
    {
        $user = User::factory()->create([
            'role' => 'buyer',
        ]);

        $response = $this->actingAs($user)->get(route('harvest.create'));

        $response->assertForbidden();
    }

    public function test_authenticated_users_can_view_the_harvest_index_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('harvest.index'));

        $response->assertOk();
    }

    public function test_authenticated_users_can_search_harvests_by_id(): void
    {
        $user = User::factory()->create();

        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Amina',
            'last_name' => 'Nabirye',
            'telephone' => '+256700000001',
            'email' => 'amina@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Bugisu Cooperative',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Bukonde Farm',
            'location' => 'Mbale, Uganda',
            'size' => '6 acres',
            'altitude' => '1,850 masl',
            'variety' => 'SL28',
        ]);

        $firstHarvest = Harvest::query()->create([
            'farm_id' => $farm->id,
            'variety' => 'SL28',
            'date_planted' => '2025-05-01',
            'harvest_date' => '2026-04-09',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1250.75,
            'ripeness_grade' => 'Premium Red Cherry',
        ]);

        $secondHarvest = Harvest::query()->create([
            'farm_id' => $farm->id,
            'variety' => 'SL28',
            'date_planted' => '2025-05-01',
            'harvest_date' => '2026-04-08',
            'pick_method' => 'Strip Picking',
            'price' => 4.15,
            'weight' => 980,
            'ripeness_grade' => 'Mostly Red Cherry',
        ]);

        $response = $this->actingAs($user)->get(route('harvest.index', [
            'search' => (string) $firstHarvest->id,
        ]));

        $response->assertOk();
        $response->assertSee(sprintf('#2026-EX%02d', $firstHarvest->id));
        $response->assertDontSee(sprintf('#2026-EX%02d', $secondHarvest->id));
    }

    public function test_authenticated_users_can_view_a_harvest_profile(): void
    {
        $user = User::factory()->create();

        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Amina',
            'last_name' => 'Nabirye',
            'telephone' => '+256700000001',
            'email' => 'amina@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Bugisu Cooperative',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Bukonde Farm',
            'location' => 'Mbale, Uganda',
            'size' => '6 acres',
            'altitude' => '1,850 masl',
            'variety' => 'SL28',
        ]);

        $harvest = Harvest::query()->create([
            'farm_id' => $farm->id,
            'variety' => 'SL28',
            'date_planted' => '2025-05-01',
            'harvest_date' => '2026-04-09',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1250.75,
            'ripeness_grade' => 'Premium Red Cherry',
        ]);

        $response = $this->actingAs($user)->get(route('harvest.show', $harvest));

        $response->assertOk();
    }

    public function test_non_admin_users_cannot_view_another_users_harvest_profile(): void
    {
        $owner = User::factory()->create([
            'role' => 'processor',
        ]);

        $viewer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $farmer = Farmer::query()->create([
            'user_id' => $owner->id,
            'first_name' => 'Amina',
            'last_name' => 'Nabirye',
            'telephone' => '+256700000001',
            'email' => 'amina@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Bugisu Cooperative',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Bukonde Farm',
            'location' => 'Mbale, Uganda',
            'size' => '6 acres',
            'altitude' => '1,850 masl',
            'variety' => 'SL28',
        ]);

        $harvest = Harvest::query()->create([
            'user_id' => $owner->id,
            'farm_id' => $farm->id,
            'variety' => 'SL28',
            'date_planted' => '2025-05-01',
            'harvest_date' => '2026-04-09',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1250.75,
            'ripeness_grade' => 'Premium Red Cherry',
        ]);

        $response = $this->actingAs($viewer)->get(route('harvest.show', $harvest));

        $response->assertForbidden();
    }

    public function test_admin_users_can_view_any_harvest_profile(): void
    {
        $owner = User::factory()->create([
            'role' => 'processor',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $farmer = Farmer::query()->create([
            'user_id' => $owner->id,
            'first_name' => 'Amina',
            'last_name' => 'Nabirye',
            'telephone' => '+256700000001',
            'email' => 'amina@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Bugisu Cooperative',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Bukonde Farm',
            'location' => 'Mbale, Uganda',
            'size' => '6 acres',
            'altitude' => '1,850 masl',
            'variety' => 'SL28',
        ]);

        $harvest = Harvest::query()->create([
            'user_id' => $owner->id,
            'farm_id' => $farm->id,
            'variety' => 'SL28',
            'date_planted' => '2025-05-01',
            'harvest_date' => '2026-04-09',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1250.75,
            'ripeness_grade' => 'Premium Red Cherry',
        ]);

        $response = $this->actingAs($admin)->get(route('harvest.show', $harvest));

        $response->assertOk();
    }

    public function test_authenticated_users_can_store_a_harvest(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Amina',
            'last_name' => 'Nabirye',
            'telephone' => '+256700000001',
            'email' => 'amina@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Bugisu Cooperative',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Bukonde Farm',
            'location' => 'Mbale, Uganda',
            'size' => '6 acres',
            'altitude' => '1,850 masl',
            'variety' => 'SL28',
        ]);

        $response = $this->actingAs($user)->post(route('harvest.store'), [
            'farm_id' => $farm->id,
            'variety' => 'SL28',
            'date_planted' => '2025-05-01',
            'harvest_date' => '2026-04-09',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1250.75,
            'ripeness_grade' => 'Premium Red Cherry',
        ]);

        $response->assertSessionHasNoErrors();

        $harvest = Harvest::query()->firstOrFail();

        $response->assertRedirect(route('harvest.show', $harvest));

        $this->assertSame($farm->id, $harvest->farm_id);
        $this->assertSame($user->id, $harvest->user_id);
        $this->assertSame('SL28', $harvest->variety);
        $this->assertSame('2025-05-01', $harvest->date_planted?->toDateString());
        $this->assertSame('2026-04-09', $harvest->harvest_date?->toDateString());
        $this->assertSame('Selective Picking', $harvest->pick_method);
        $this->assertSame('4.85', $harvest->price);
        $this->assertSame('1250.75', $harvest->weight);
        $this->assertSame('Premium Red Cherry', $harvest->ripeness_grade);
    }

    public function test_non_admin_users_cannot_store_a_harvest(): void
    {
        $user = User::factory()->create([
            'role' => 'buyer',
        ]);

        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Amina',
            'last_name' => 'Nabirye',
            'telephone' => '+256700000001',
            'email' => 'amina@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Bugisu Cooperative',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Bukonde Farm',
            'location' => 'Mbale, Uganda',
            'size' => '6 acres',
            'altitude' => '1,850 masl',
            'variety' => 'SL28',
        ]);

        $response = $this->actingAs($user)->post(route('harvest.store'), [
            'farm_id' => $farm->id,
            'variety' => 'SL28',
            'date_planted' => '2025-05-01',
            'harvest_date' => '2026-04-09',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1250.75,
            'ripeness_grade' => 'Premium Red Cherry',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('harvests', 0);
    }

    public function test_harvest_dates_cannot_be_in_the_future(): void
    {
        Carbon::setTestNow('2026-04-10 10:00:00');

        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Amina',
            'last_name' => 'Nabirye',
            'telephone' => '+256700000001',
            'email' => 'amina@example.com',
            'district' => 'Mbale',
            'sub_county' => 'Bungokho',
            'coffee_type' => 'Arabica',
            'cooperative' => 'Bugisu Cooperative',
            'farm_size' => '6 acres',
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Bukonde Farm',
            'location' => 'Mbale, Uganda',
            'size' => '6 acres',
            'altitude' => '1,850 masl',
            'variety' => 'SL28',
        ]);

        $response = $this->actingAs($user)->post(route('harvest.store'), [
            'farm_id' => $farm->id,
            'variety' => 'SL28',
            'date_planted' => '2026-05-01',
            'harvest_date' => '2026-04-11',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1250.75,
            'ripeness_grade' => 'Premium Red Cherry',
        ]);

        $response->assertSessionHasErrors([
            'date_planted' => 'Date planted must not be greater than today.',
            'harvest_date' => 'Harvest date must not be greater than today.',
        ]);

        $this->assertDatabaseCount('harvests', 0);

        Carbon::setTestNow();
    }
}
