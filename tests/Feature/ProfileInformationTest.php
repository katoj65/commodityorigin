<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put('/user/profile-information', [
            'first_name' => 'Test',
            'last_name' => 'Name',
            'role' => 'buyer',
            'telephone' => '+256700000000',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Test', $user->fresh()->first_name);
        $this->assertEquals('Name', $user->fresh()->last_name);
        $this->assertEquals('buyer', $user->fresh()->role);
        $this->assertEquals('+256700000000', $user->fresh()->telephone);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }
}
