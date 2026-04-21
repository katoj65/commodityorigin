<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BatchCompliance;
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

    public function test_the_creator_can_store_batch_compliance(): void
    {
        $creator = User::factory()->create();

        $batch = Batch::query()->create([
            'user_id' => $creator->id,
            'batch_number' => 'BATCH-2026-003',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 10,
            'weight' => 600,
            'status' => 'received',
        ]);

        $response = $this->actingAs($creator)->post(route('batch.compliance.store', $batch), [
            'compliance_type' => 'Organic certificate',
            'status' => 'approved',
            'certificate_number' => 'ORG-2026-009',
            'issued_by' => 'Commodity Origin Lab',
            'issued_at' => '2026-04-20',
            'expires_at' => '2027-04-20',
            'notes' => 'Approved after document review.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('batch_compliances', [
            'batch_id' => $batch->id,
            'user_id' => $creator->id,
            'compliance_type' => 'Organic certificate',
            'status' => 'approved',
            'certificate_number' => 'ORG-2026-009',
            'issued_by' => 'Commodity Origin Lab',
        ]);
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

    public function test_non_creators_cannot_store_batch_compliance(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();

        $batch = Batch::query()->create([
            'user_id' => $creator->id,
            'batch_number' => 'BATCH-2026-004',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 10,
            'weight' => 600,
            'status' => 'received',
        ]);

        $this->actingAs($otherUser)
            ->post(route('batch.compliance.store', $batch), [
                'compliance_type' => 'Organic certificate',
                'status' => 'approved',
            ])
            ->assertForbidden();

        $this->assertSame(0, BatchCompliance::query()->count());
    }
}
