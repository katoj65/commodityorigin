<?php

namespace Tests\Feature;

use App\Models\MarketIntelligenceArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MarketIntelligencePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_is_public_and_lists_published_articles_newest_first(): void
    {
        MarketIntelligenceArticle::query()->create([
            'slug' => 'older-article',
            'title' => 'Older Article',
            'excerpt' => 'Older excerpt.',
            'category' => 'Education',
            'is_published' => true,
            'published_at' => now()->subDays(2),
        ]);

        MarketIntelligenceArticle::query()->create([
            'slug' => 'newer-article',
            'title' => 'Newer Article',
            'excerpt' => 'Newer excerpt.',
            'category' => 'Platform Update',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        MarketIntelligenceArticle::query()->create([
            'slug' => 'draft-article',
            'title' => 'Draft Article',
            'excerpt' => 'Should not appear.',
            'category' => 'Education',
            'is_published' => false,
            'published_at' => null,
        ]);

        $response = $this->get(route('market-intelligence.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('MarketIntelligence/Index')
            ->has('articles', 2)
            ->where('articles.0.title', 'Newer Article')
            ->where('articles.1.title', 'Older Article')
            ->has('categories', 2)
        );
    }
}
