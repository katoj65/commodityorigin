<?php

namespace Tests\Unit;

use App\Models\Batch;
use App\Models\Lot;
use App\Models\User;
use App\Policies\LotPolicy;
use Tests\TestCase;

class LotPolicyTest extends TestCase
{
    public function test_admin_can_create_lots_without_a_batch_context(): void
    {
        $policy = new LotPolicy();
        $admin = User::factory()->make([
            'role' => 'admin',
        ]);

        $this->assertTrue($policy->create($admin));
    }

    public function test_batch_creator_can_create_lots_from_their_batch(): void
    {
        $policy = new LotPolicy();
        $user = User::factory()->make([
            'id' => 10,
            'role' => 'processor',
        ]);
        $batch = new Batch([
            'user_id' => 10,
        ]);

        $this->assertTrue($policy->create($user, $batch));
    }

    public function test_non_creator_cannot_create_lots_from_another_batch(): void
    {
        $policy = new LotPolicy();
        $user = User::factory()->make([
            'id' => 10,
            'role' => 'processor',
        ]);
        $batch = new Batch([
            'user_id' => 11,
        ]);

        $this->assertFalse($policy->create($user, $batch));
    }

    public function test_creator_can_update_and_delete_their_lot(): void
    {
        $policy = new LotPolicy();
        $user = User::factory()->make([
            'id' => 10,
            'role' => 'processor',
        ]);
        $lot = new Lot([
            'user_id' => 10,
        ]);

        $this->assertTrue($policy->update($user, $lot));
        $this->assertTrue($policy->delete($user, $lot));
    }

    public function test_admin_can_manage_any_lot(): void
    {
        $policy = new LotPolicy();
        $admin = User::factory()->make([
            'id' => 1,
            'role' => 'admin',
        ]);
        $lot = new Lot([
            'user_id' => 10,
        ]);

        $this->assertTrue($policy->view($admin, $lot));
        $this->assertTrue($policy->update($admin, $lot));
        $this->assertTrue($policy->delete($admin, $lot));
    }
}
