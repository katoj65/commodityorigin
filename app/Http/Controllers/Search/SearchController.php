<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketListingResource;
use App\Http\Resources\SearchLogResource;
use App\Models\Market;
use App\Models\SearchLog;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $search,
    ) {
    }

    /**
     * Search live market items by keyword and optional filters. Logs the
     * search against the authenticated user, skipping empty queries.
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'origin' => ['nullable', 'string', 'max:100'],
            'demand' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $keyword = $validated['q'] ?? '';
        $filters = collect($validated)->except('q')->filter()->all();

        $results = $keyword === '' ? collect() : $this->search->search($keyword, $filters);

        $this->search->log($request->user()->id, $keyword, $results->count(), $filters);

        return Inertia::render('Search/SearchPage', [
            'query' => $keyword,
            'filters' => $filters,
            'results' => MarketListingResource::collection($results)->resolve(),
            'recentSearches' => SearchLogResource::collection($this->search->recentForUser($request->user()->id))->resolve(),
        ]);
    }

    /**
     * Typeahead suggestions for the search bar — a small JSON payload of
     * matching live market items as the user types.
     */
    public function suggest(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->query('q', ''));

        if ($keyword === '') {
            return response()->json(['results' => []]);
        }

        $results = $this->search->suggest($keyword)->map(fn (Market $market) => [
            'id' => $market->id,
            'name' => $market->title,
            'lot_code' => $market->lot_code,
            'origin' => $market->origin,
            'type' => $market->type,
            'price_per_kg' => (float) $market->price_per_unit,
            'demand' => $market->demand,
        ])->values();

        return response()->json(['results' => $results]);
    }

    /**
     * Delete a single search history entry.
     */
    public function destroy(Request $request, SearchLog $log): RedirectResponse
    {
        if ($log->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this search.');
        }

        $this->search->destroy($log);

        return back();
    }

    /**
     * Clear all search history for the authenticated user.
     */
    public function clearHistory(Request $request): RedirectResponse
    {
        $this->search->clearForUser($request->user()->id);

        return back();
    }
}
