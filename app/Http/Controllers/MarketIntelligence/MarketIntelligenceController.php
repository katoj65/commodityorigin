<?php

namespace App\Http\Controllers\MarketIntelligence;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketIntelligenceArticleResource;
use App\Services\MarketIntelligenceService;
use Inertia\Inertia;
use Inertia\Response;

class MarketIntelligenceController extends Controller
{
    public function __construct(private readonly MarketIntelligenceService $intelligence)
    {
    }

    /**
     * Display the public Market Intelligence feed.
     */
    public function index(): Response
    {
        return Inertia::render('MarketIntelligence/Index', [
            'articles' => MarketIntelligenceArticleResource::collection($this->intelligence->published())->resolve(),
            'categories' => $this->intelligence->categories(),
        ]);
    }
}
