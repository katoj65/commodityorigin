<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BatchOwnership;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\Harvest;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchCreateLotTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_creator_can_view_the_batch_lot_creation_page(): void
    {
        $context = $this->makeBatchCreationContext();

        $this->actingAs($context['user'])
            ->get(route('batch.create-lot', $context['batch']))
            ->assertOk();
    }

    public function test_non_creator_cannot_view_the_batch_lot_creation_page(): void
    {
        $context = $this->makeBatchCreationContext();
        $otherUser = User::factory()->create([
            'role' => 'processor',
        ]);

        $this->actingAs($otherUser)
            ->get(route('batch.create-lot', $context['batch']))
            ->assertForbidden();
    }

    public function test_batch_creator_can_store_a_lot_from_the_batch(): void
    {
        $context = $this->makeBatchCreationContext();

        $response = $this->actingAs($context['user'])
            ->post(route('batch.store-lot', $context['batch']), [
                'lot_number' => 'LOT-BATCH-2026-01',
                'lot_name' => 'Sidama Bensa - Special Selection',
                'allocation_kg' => 400,
                'quantity_bags' => 10,
                'bag_weight_kg' => 60,
                'grade' => 'A1',
                'warehouse' => 'Addis Ababa Central',
                'packaging_type' => 'GrainPro',
                'screen_size' => '17/18',
                'altitude' => '1,950 - 2,100',
                'aroma_score' => 8.75,
                'acidity_score' => 9.00,
                'body_score' => 8.25,
                'target_market' => 'United Arab Emirates (Specialty)',
                'price_per_kg' => 12.50,
                'tokenize' => true,
                'submission_intent' => 'create_and_tokenise',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('batch.show', $context['batch']));

        $lot = Lot::query()->where('lot_number', 'LOT-BATCH-2026-01')->firstOrFail();

        $this->assertSame($context['batch']->id, $lot->batch_id);
        $this->assertSame($context['user']->id, $lot->user_id);
        $this->assertSame('400.00', (string) $lot->net_weight_kg);

        $this->assertDatabaseHas('lots', [
            'id' => $lot->id,
            'lot_name' => 'Sidama Bensa - Special Selection',
            'allocation_kg' => 400,
            'net_weight_kg' => 400,
            'packaging_type' => 'GrainPro',
            'target_market' => 'United Arab Emirates (Specialty)',
            'price_per_kg' => 12.50,
            'tokenize' => 1,
            'status' => 'tokenisation_ready',
        ]);
    }

    public function test_admin_can_create_a_lot_from_another_users_batch(): void
    {
        $context = $this->makeBatchCreationContext();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('batch.store-lot', $context['batch']), [
                'lot_number' => 'LOT-BATCH-2026-02',
                'lot_name' => 'Admin Managed Lot',
                'allocation_kg' => 250,
                'quantity_bags' => 5,
                'bag_weight_kg' => 50,
                'grade' => 'A1',
                'warehouse' => 'Kampala Export Hub',
                'packaging_type' => 'Vacuum',
                'screen_size' => '16/18',
                'altitude' => '1,950 - 2,100',
                'aroma_score' => 8.5,
                'acidity_score' => 8.8,
                'body_score' => 8.2,
                'target_market' => 'Japan Premium Roasters',
                'price_per_kg' => 11.90,
                'tokenize' => false,
                'submission_intent' => 'create',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('batch.show', $context['batch']));

        $this->assertDatabaseHas('lots', [
            'lot_number' => 'LOT-BATCH-2026-02',
            'user_id' => $admin->id,
            'batch_id' => $context['batch']->id,
            'status' => 'ready',
        ]);
    }

    /**
     * @return array{user: User, batch: Batch, farm: Farm, harvest: Harvest}
     */
    private function makeBatchCreationContext(): array
    {
        $user = User::factory()->create([
            'role' => 'processor',
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
            'name' => 'Sidama Bensa',
            'location' => 'Ethiopia',
            'size' => '6 acres',
            'altitude' => '1,950 - 2,100',
            'variety' => 'Heirloom Arabica',
            'latitude' => 1.2167,
            'longitude' => 34.4167,
        ]);

        $harvest = Harvest::query()->create([
            'user_id' => $user->id,
            'farm_id' => $farm->id,
            'variety' => 'Heirloom Arabica',
            'date_planted' => '2026-01-14',
            'harvest_date' => '2026-04-20',
            'harvest_season' => 'Main Crop',
            'status' => 'active',
            'pick_method' => 'Selective Picking',
            'price' => 4.85,
            'weight' => 1000,
            'ripeness_percentage' => 94,
            'foreign_matter_present' => false,
            'pest_damage' => false,
            'disease_signs' => false,
            'visible_defects' => false,
        ]);

        $batch = Batch::query()->create([
            'user_id' => $user->id,
            'batch_number' => 'BTC-2026-08',
            'variety' => 'Heirloom Arabica',
            'warehouse_location' => 'Addis Ababa Central',
            'quantity' => 20,
            'weight' => 1000,
            'price' => 12500,
            'moisture_content' => 11.2,
            'processing_date' => '2026-04-20',
            'processing_method' => 'Washed',
            'drying_method' => 'Raised Beds',
            'screen_size' => '17/18',
            'cup_score' => 87.2,
            'status' => 'received',
        ]);

        BatchOwnership::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $user->id,
            'owner_id' => $harvest->id,
            'owner_type' => Harvest::class,
        ]);

        return compact('user', 'batch', 'farm', 'harvest');
    }
}
