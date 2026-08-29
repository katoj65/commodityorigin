<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\Harvest;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_the_batch_index_page(): void
    {
        $user = User::factory()->create();

        Batch::query()->create([
            'user_id' => $user->id,
            'batch_number' => 'BATCH-2026-001',
            'variety' => 'Bourbon',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 12,
            'weight' => 720.5,
            'moisture_content' => 11.2,
            'status' => 'received',
            'notes' => 'Initial intake for quality review.',
        ]);

        $response = $this->actingAs($user)->get(route('batch.index'));

        $response->assertOk();
        $response->assertSee('BATCH-2026-001');
        $response->assertSee('Kampala Warehouse');
    }

    public function test_authenticated_users_can_store_a_batch(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('batch.store'), [
            'batch_number' => 'BATCH-2026-001',
            'variety' => 'Bourbon',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity_bags' => 12,
            'net_weight_kg' => 720.5,
            'price' => 2450.75,
            'moisture_content' => 11.2,
            'processing_date' => '2026-04-20',
            'processing_method' => 'Washed',
            'drying_method' => 'Raised beds',
            'drying_duration' => 14,
            'milling_status' => 'Milled',
            'screen_size' => '16/18',
            'defect_count' => 8,
            'cup_score' => 86.75,
            'notes' => 'Initial intake for quality review.',
        ]);

        $response->assertSessionHasNoErrors();
        $batch = \App\Models\Batch::query()->where('batch_number', 'BATCH-2026-001')->firstOrFail();

        $response->assertRedirect(route('batch.show', $batch));

        $this->assertSame('2026-04-20', $batch->processing_date->toDateString());

        $this->assertDatabaseHas('batches', [
            'user_id' => $user->id,
            'batch_number' => 'BATCH-2026-001',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 12,
            'weight' => 720.5,
            'price' => 2450.75,
            'moisture_content' => 11.2,
            'processing_method' => 'Washed',
            'drying_method' => 'Raised beds',
            'drying_duration' => 14,
            'milling_status' => 'Milled',
            'screen_size' => '16/18',
            'defect_count' => 8,
            'cup_score' => 86.75,
            'status' => 'received',
            'notes' => 'Initial intake for quality review.',
        ]);
    }

    public function test_authenticated_users_can_store_a_batch_with_linked_season_and_harvests(): void
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
            'tel' => '+256700000001',
            'email' => 'samuel@example.com',
            'district' => 'Mbale',
            'subcounty' => 'Bungokho',
            'farmer_number' => 'FMR-TEST-'.uniqid(),
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

        $firstHarvest = Harvest::query()->create([
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

        $secondHarvest = Harvest::query()->create([
            'user_id' => $user->id,
            'season_id' => $season->id,
            'farm_id' => $farm->id,
            'variety' => 'Arabica SL34',
            'date_planted' => '2026-04-18',
            'harvest_date' => '2026-04-24',
            'harvest_season' => 'Main Crop',
            'status' => 'active',
            'pick_method' => 'Strip Picking',
            'price' => 4.9,
            'weight' => 1180,
            'ripeness_percentage' => 92,
            'foreign_matter_present' => false,
            'pest_damage' => false,
            'disease_signs' => false,
            'visible_defects' => false,
        ]);

        $response = $this->actingAs($user)->post(route('batch.store'), [
            'batch_number' => 'BATCH-2026-101',
            'variety' => 'Arabica',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity_bags' => 18,
            'net_weight_kg' => 2430,
            'price' => 3650,
            'moisture_content' => 11.2,
            'processing_date' => '2026-04-20',
            'processing_method' => 'Washed',
            'drying_method' => 'Raised beds',
            'drying_duration' => 14,
            'milling_status' => 'Milled',
            'screen_size' => '16/18',
            'defect_count' => 4,
            'cup_score' => 87.2,
            'notes' => 'Season-linked batch',
            'season_id' => $season->id,
            'harvest_ids' => [$firstHarvest->id, $secondHarvest->id],
        ]);

        $batch = Batch::query()->where('batch_number', 'BATCH-2026-101')->firstOrFail();

        $response->assertRedirect(route('batch.show', $batch));

        $this->assertSame($season->id, $batch->season_id);

        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'season_id' => $season->id,
        ]);
    }

    public function test_only_the_creator_can_view_the_batch_profile(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();

        $batch = Batch::query()->create([
            'user_id' => $creator->id,
            'batch_number' => 'BATCH-2026-002',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 8,
            'weight' => 480,
            'status' => 'received',
        ]);

        $this->actingAs($creator)
            ->get(route('batch.show', $batch))
            ->assertOk();

        $this->actingAs($otherUser)
            ->get(route('batch.show', $batch))
            ->assertForbidden();
    }

    public function test_batch_profile_queries_the_linked_season_and_harvests_payload(): void
    {
        $creator = User::factory()->create([
            'role' => 'admin',
        ]);

        $season = Season::query()->create([
            'user_id' => $creator->id,
            'name' => 'Main Crop 2026',
            'region' => 'Mount Elgon',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $farmer = Farmer::query()->create([
            'user_id' => $creator->id,
            'first_name' => 'Abebe',
            'last_name' => 'Bikila',
            'tel' => '+256700000002',
            'email' => 'abebe@example.com',
            'district' => 'Mbale',
            'subcounty' => 'Bungokho',
            'farmer_number' => 'FMR-TEST-'.uniqid(),
        ]);

        $farm = Farm::query()->create([
            'farmer_id' => $farmer->id,
            'name' => 'Mount Elgon Heights',
            'location' => 'Mount Elgon',
            'size' => '8 acres',
            'altitude' => '2100m',
            'variety' => 'Arabica SL14',
            'latitude' => 1.1490,
            'longitude' => 34.3310,
        ]);

        $harvest = Harvest::query()->create([
            'user_id' => $creator->id,
            'season_id' => $season->id,
            'farm_id' => $farm->id,
            'variety' => 'Arabica SL14',
            'date_planted' => '2026-04-12',
            'harvest_date' => '2026-04-22',
            'harvest_season' => 'Main Crop',
            'status' => 'active',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 4200,
            'ripeness_percentage' => 94,
            'foreign_matter_present' => false,
            'pest_damage' => false,
            'disease_signs' => false,
            'visible_defects' => false,
        ]);

        $batch = Batch::query()->create([
            'user_id' => $creator->id,
            'season_id' => $season->id,
            'batch_number' => 'BATCH-2026-200',
            'variety' => 'Arabica SL-14',
            'warehouse_location' => 'Ventilated Silo',
            'quantity' => 12,
            'weight' => 4200,
            'price' => 5400,
            'moisture_content' => 11.2,
            'processing_date' => '2026-04-20',
            'processing_method' => 'Washed',
            'drying_method' => 'Raised African Beds',
            'drying_duration' => 36,
            'defect_count' => 2,
            'cup_score' => 87.2,
            'status' => 'verified',
        ]);

        $response = $this->actingAs($creator)->get(route('batch.show', $batch));

        $response->assertOk();
        $response->assertSee('Batch/BatchProfile');
        $response->assertSee('Main Crop 2026');
        $response->assertSee('Mount Elgon Heights');
    }

    public function test_the_creator_can_update_batch_data(): void
    {
        $creator = User::factory()->create();

        $batch = Batch::query()->create([
            'user_id' => $creator->id,
            'batch_number' => 'BATCH-2026-005',
            'variety' => 'Bourbon',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 10,
            'weight' => 600,
            'price' => 2100,
            'processing_date' => '2026-04-19',
            'processing_method' => 'Natural',
            'drying_method' => 'Patio',
            'status' => 'received',
        ]);

        $response = $this->actingAs($creator)->patch(route('batch.update', $batch), [
            'batch_number' => 'BATCH-2026-005-UPDATED',
            'variety' => 'Geisha',
            'warehouse_location' => 'Entebbe Warehouse',
            'quantity_bags' => 14,
            'net_weight_kg' => 820.5,
            'price' => 2780.25,
            'moisture_content' => 10.8,
            'processing_date' => '2026-04-20',
            'processing_method' => 'Washed',
            'drying_method' => 'Raised beds',
            'drying_duration' => 12,
            'milling_status' => 'Ready for grading',
            'screen_size' => '17/18',
            'defect_count' => 5,
            'cup_score' => 89.25,
            'notes' => 'Updated after warehouse review.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'batch_number' => 'BATCH-2026-005-UPDATED',
            'variety' => 'Geisha',
            'warehouse_location' => 'Entebbe Warehouse',
            'quantity' => 14,
            'weight' => 820.5,
            'price' => 2780.25,
            'processing_method' => 'Washed',
            'drying_method' => 'Raised beds',
            'milling_status' => 'Ready for grading',
        ]);
    }

    public function test_non_creators_cannot_update_batch_data(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();

        $batch = Batch::query()->create([
            'user_id' => $creator->id,
            'batch_number' => 'BATCH-2026-006',
            'variety' => 'Bourbon',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 10,
            'weight' => 600,
            'price' => 2100,
            'processing_date' => '2026-04-19',
            'processing_method' => 'Natural',
            'drying_method' => 'Patio',
            'status' => 'received',
        ]);

        $this->actingAs($otherUser)
            ->patch(route('batch.update', $batch), [
                'batch_number' => 'BATCH-2026-006-UPDATED',
                'variety' => 'Geisha',
                'warehouse_location' => 'Entebbe Warehouse',
                'quantity_bags' => 14,
                'net_weight_kg' => 820.5,
                'price' => 2780.25,
                'processing_date' => '2026-04-20',
                'processing_method' => 'Washed',
                'drying_method' => 'Raised beds',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'batch_number' => 'BATCH-2026-006',
        ]);
    }

}
