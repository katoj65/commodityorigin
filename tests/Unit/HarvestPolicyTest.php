<?php

namespace Tests\Unit;

use App\Models\Harvest;
use App\Models\User;
use App\Policies\HarvestPolicy;
use Tests\TestCase;

class HarvestPolicyTest extends TestCase
{
    public function test_admin_can_create_harvests(): void
    {
        $policy = new HarvestPolicy();
        $admin = User::factory()->make([
            'role' => 'admin',
        ]);

        $this->assertTrue($policy->create($admin));
    }

    public function test_non_admin_cannot_create_harvests(): void
    {
        $policy = new HarvestPolicy();
        $user = User::factory()->make([
            'role' => 'buyer',
        ]);

        $this->assertFalse($policy->create($user));
    }

    public function test_creator_can_update_and_delete_harvest(): void
    {
        $policy = new HarvestPolicy();
        $user = User::factory()->make([
            'id' => 10,
            'role' => 'processor',
        ]);
        $harvest = new Harvest([
            'user_id' => 10,
        ]);

        $this->assertTrue($policy->update($user, $harvest));
        $this->assertTrue($policy->delete($user, $harvest));
    }

    public function test_non_creator_cannot_update_or_delete_harvest(): void
    {
        $policy = new HarvestPolicy();
        $user = User::factory()->make([
            'id' => 10,
            'role' => 'processor',
        ]);
        $harvest = new Harvest([
            'user_id' => 11,
        ]);

        $this->assertFalse($policy->update($user, $harvest));
        $this->assertFalse($policy->delete($user, $harvest));
    }
}
