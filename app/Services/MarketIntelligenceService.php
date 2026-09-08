<?php

namespace App\Services;

use App\Models\MarketIntelligenceArticle;
use Illuminate\Database\Eloquent\Collection;

class MarketIntelligenceService
{
    /**
     * Fetch every published article, newest first.
     *
     * @return Collection<int, MarketIntelligenceArticle>
     */
    public function published(): Collection
    {
        return MarketIntelligenceArticle::query()
            ->published()
            ->with('author')
            ->get();
    }

    /**
     * Find a single published article by its slug.
     */
    public function findPublished(string $slug): ?MarketIntelligenceArticle
    {
        return MarketIntelligenceArticle::query()
            ->published()
            ->with('author')
            ->where('slug', $slug)
            ->first();
    }

    /**
     * The distinct categories in use across published articles.
     *
     * @return array<int, string>
     */
    public function categories(): array
    {
        return MarketIntelligenceArticle::query()
            ->where('is_published', true)
            ->pluck('category')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
