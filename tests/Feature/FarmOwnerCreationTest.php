<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\FarmOwner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FarmOwnerCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_owner_toggle_records_the_authenticated_users_own_details(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'telephone' => '+256700111222',
        ]);

        $response = $this->actingAs($user)->post(route('farm.store'), [
            'name' => 'Self-Owned Estate',
            'is_self_owner' => true,
            'owner_ownership_percentage' => 100,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('farm_owners', 1);

        $owner = FarmOwner::query()->first();

        $this->assertSame('Jane', $owner->first_name);
        $this->assertNull($owner->middle_name);
        $this->assertSame('Doe', $owner->last_name);
        $this->assertSame('Jane Doe', $owner->name);
        $this->assertSame('jane@example.com', $owner->email);
        $this->assertSame('+256700111222', $owner->tel);
        $this->assertNull($owner->national_id);
        $this->assertTrue($owner->is_primary);
        $this->assertSame(100.0, (float) $owner->ownership_percentage);
        $this->assertSame($user->id, $owner->user_id);
    }

    public function test_other_owner_toggle_records_the_submitted_owner_details(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('farm.store'), [
            'name' => 'Family-Owned Estate',
            'is_self_owner' => false,
            'owner_first_name' => 'Peter',
            'owner_middle_name' => 'Kato',
            'owner_last_name' => 'Okello',
            'owner_national_id' => 'CM123456789ABC',
            'owner_tel' => '+256700333444',
            'owner_email' => 'peter@example.com',
            'owner_ownership_percentage' => 60,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('farm_owners', 1);

        $owner = FarmOwner::query()->first();

        $this->assertSame('Peter', $owner->first_name);
        $this->assertSame('Kato', $owner->middle_name);
        $this->assertSame('Okello', $owner->last_name);
        $this->assertSame('Peter Kato Okello', $owner->name);
        $this->assertSame('CM123456789ABC', $owner->national_id);
        $this->assertSame('+256700333444', $owner->tel);
        $this->assertSame('peter@example.com', $owner->email);
        $this->assertTrue($owner->is_primary);
        $this->assertSame(60.0, (float) $owner->ownership_percentage);
        $this->assertSame($user->id, $owner->user_id, 'user_id records who recorded the entry, not the owner identity');
    }

    public function test_farm_profile_page_reflects_owners_from_the_farm_owners_table(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post(route('farm.store'), [
            'name' => 'Reflected Estate',
            'is_self_owner' => false,
            'owner_first_name' => 'Grace',
            'owner_last_name' => 'Auma',
            'owner_ownership_percentage' => 75,
        ]);

        $farm = Farm::query()->where('name', 'Reflected Estate')->firstOrFail();

        $response = $this->actingAs($user)->get(route('farm.show', $farm));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Farm/FarmProfile')
            ->has('owners', 1)
            ->where('owners.0.first_name', 'Grace')
            ->where('owners.0.last_name', 'Auma')
            ->where('owners.0.name', 'Grace Auma')
            ->where('owners.0.is_primary', true)
        );
    }

    public function test_other_owner_toggle_requires_first_and_last_name(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('farm.store'), [
            'name' => 'Nameless Owner Estate',
            'is_self_owner' => false,
        ]);

        $response->assertSessionHasErrors(['owner_first_name', 'owner_last_name']);
        $this->assertDatabaseCount('farm_owners', 0);
        $this->assertDatabaseCount('farms', 0);
    }
}
