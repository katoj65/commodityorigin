<?php

namespace Tests\Feature;

use App\Models\HowItWorksStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HowItWorksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_is_public_and_lists_active_steps_in_order(): void
    {
        HowItWorksStep::query()->create([
            'slug' => 'second-step',
            'title' => 'Second Step',
            'description' => 'The second thing that happens.',
            'icon' => 'looks_two',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        HowItWorksStep::query()->create([
            'slug' => 'first-step',
            'title' => 'First Step',
            'description' => 'The first thing that happens.',
            'icon' => 'looks_one',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        HowItWorksStep::query()->create([
            'slug' => 'hidden-step',
            'title' => 'Hidden Step',
            'description' => 'Should not appear.',
            'sort_order' => 3,
            'is_active' => false,
        ]);

        $response = $this->get(route('how-it-works.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('HowItWorks/Index')
            ->has('steps', 2)
            ->where('steps.0.title', 'First Step')
            ->where('steps.1.title', 'Second Step')
        );
    }
}
