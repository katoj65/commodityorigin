<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_store_a_batch(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('batch.store'), [
            'batch_number' => 'BATCH-2026-001',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity_bags' => 12,
            'net_weight_kg' => 720.5,
            'moisture_content' => 11.2,
            'notes' => 'Initial intake for quality review.',
        ]);

        $response->assertSessionHasNoErrors();
        $batch = \App\Models\Batch::query()->where('batch_number', 'BATCH-2026-001')->firstOrFail();

        $response->assertRedirect(route('batch.show', $batch));

        $this->assertDatabaseHas('batches', [
            'user_id' => $user->id,
            'batch_number' => 'BATCH-2026-001',
            'warehouse_location' => 'Kampala Warehouse',
            'quantity' => 12,
            'weight' => 720.5,
            'moisture_content' => 11.2,
            'status' => 'received',
            'notes' => 'Initial intake for quality review.',
        ]);
    }
}
