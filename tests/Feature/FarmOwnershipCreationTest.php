<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\User;
use App\Models\UserDesignationMetadata;
use App\Models\UserFarmOwnership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FarmOwnershipCreationTest extends TestCase
{
    use RefreshDatabase;

    private function seedFarmerDesignation(): void
    {
        UserDesignationMetadata::query()->create([
            'slug' => 'farmer',
            'name' => 'Farmer',
            'description' => 'Grows and supplies coffee.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_self_owner_toggle_links_the_authenticated_user_via_the_pivot(): void
    {
        $this->seedFarmerDesignation();

        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('farm.store'), [
            'name' => 'Self-Owned Estate',
            'is_self_owner' => true,
            'owner_ownership_percentage' => 100,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('user_farm_ownership', 1);
        $this->assertDatabaseCount('users', 1);

        $pivot = UserFarmOwnership::query()->first();

        $this->assertSame($user->id, $pivot->user_id);
        $this->assertTrue($pivot->is_primary);
        $this->assertSame(100.0, (float) $pivot->ownership_percentage);

        $user->refresh();
        $this->assertSame('farmer', $user->designation?->slug);
    }

    public function test_other_owner_toggle_creates_a_new_user_account_designated_as_farmer(): void
    {
        $this->seedFarmerDesignation();

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
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('user_farm_ownership', 1);

        $owner = User::query()->where('email', 'peter@example.com')->firstOrFail();

        $this->assertSame('Peter', $owner->first_name);
        $this->assertSame('Kato Okello', $owner->last_name);
        $this->assertSame('CM123456789ABC', $owner->national_id);
        $this->assertSame('+256700333444', $owner->telephone);
        $this->assertSame('farmer', $owner->designation?->slug);
        $this->assertSame($user->id, $owner->created_by_user_id);

        $pivot = UserFarmOwnership::query()->first();
        $this->assertSame($owner->id, $pivot->user_id);
        $this->assertTrue($pivot->is_primary);
        $this->assertSame(60.0, (float) $pivot->ownership_percentage);
    }

    public function test_other_owner_toggle_reuses_an_existing_user_found_by_email(): void
    {
        $this->seedFarmerDesignation();

        $user = User::factory()->create(['role' => 'admin']);
        $existingOwner = User::factory()->create(['email' => 'grace@example.com']);

        $response = $this->actingAs($user)->post(route('farm.store'), [
            'name' => 'Reused Owner Estate',
            'is_self_owner' => false,
            'owner_first_name' => 'Grace',
            'owner_last_name' => 'Auma',
            'owner_tel' => '+256700555666',
            'owner_email' => 'grace@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('user_farm_ownership', 1);

        $pivot = UserFarmOwnership::query()->first();
        $this->assertSame($existingOwner->id, $pivot->user_id);

        $existingOwner->refresh();
        $this->assertSame('farmer', $existingOwner->designation?->slug);
    }

    public function test_other_owner_toggle_requires_first_last_name_email_and_phone(): void
    {
        $this->seedFarmerDesignation();

        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('farm.store'), [
            'name' => 'Incomplete Owner Estate',
            'is_self_owner' => false,
        ]);

        $response->assertSessionHasErrors(['owner_first_name', 'owner_last_name', 'owner_tel', 'owner_email']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('farms', 0);
        $this->assertDatabaseCount('user_farm_ownership', 0);
    }

    public function test_farm_profile_page_reflects_ownership_from_the_pivot_table(): void
    {
        $this->seedFarmerDesignation();

        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post(route('farm.store'), [
            'name' => 'Reflected Estate',
            'is_self_owner' => false,
            'owner_first_name' => 'Grace',
            'owner_last_name' => 'Auma',
            'owner_tel' => '+256700555666',
            'owner_email' => 'grace@example.com',
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
            ->where('owners.0.email', 'grace@example.com')
            ->where('owners.0.is_primary', true)
        );
    }
}
