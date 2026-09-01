<?php

namespace App\Services;

use App\Models\Market;
use App\Models\SearchLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SearchService
{
    /**
     * Get a base query builder for search logs.
     */
    public function query(): Builder
    {
        return SearchLog::query();
    }

    /**
     * Search live market items by keyword, with optional filters.
     *
     * @param  array{type?: string, origin?: string, demand?: string, min_price?: float, max_price?: float}  $filters
     * @return Collection<int, Market>
     */
    public function search(string $keyword, array $filters = []): Collection
    {
        $keyword = trim($keyword);

        return Market::query()
            ->where('status', 'live')
            // title/description are real columns; origin/type/process/
            // target_market/demand/lot_code live in `metadata` (or, for
            // lot_code, on the linked lot itself) since the markets table
            // restructure — see Market's metadata-backed accessors.
            ->when($keyword !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('metadata->origin', 'like', "%{$keyword}%")
                ->orWhere('metadata->type', 'like', "%{$keyword}%")
                ->orWhere('metadata->process', 'like', "%{$keyword}%")
                ->orWhere('metadata->target_market', 'like', "%{$keyword}%")
                ->orWhere('metadata->lot_code', 'like', "%{$keyword}%")
                ->orWhereHas('lot', fn (Builder $lotQuery) => $lotQuery->where('lot_number', 'like', "%{$keyword}%"))))
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('metadata->type', $type))
            ->when($filters['origin'] ?? null, fn (Builder $q, string $origin) => $q->where('metadata->origin', $origin))
            ->when($filters['demand'] ?? null, fn (Builder $q, string $demand) => $q->where('metadata->demand', $demand))
            ->when($filters['min_price'] ?? null, fn (Builder $q, $min) => $q->where('price_per_unit', '>=', $min))
            ->when($filters['max_price'] ?? null, fn (Builder $q, $max) => $q->where('price_per_unit', '<=', $max))
            ->with('lot.images')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Lightweight lookup for the search-bar typeahead — same matching rules
     * as search(), capped to a handful of results.
     *
     * @return Collection<int, Market>
     */
    public function suggest(string $keyword, int $limit = 6): Collection
    {
        return $this->search($keyword)->take($limit);
    }

    /**
     * Record a search — skips empty-keyword, filter-only browsing so the
     * history only reflects actual searches.
     *
     * @param  array<string, mixed>  $filters
     */
    public function log(int $userId, string $keyword, int $resultsCount, array $filters = []): ?SearchLog
    {
        if (trim($keyword) === '') {
            return null;
        }

        return SearchLog::create([
            'user_id' => $userId,
            'query' => $keyword,
            'filters' => $filters,
            'results_count' => $resultsCount,
        ]);
    }

    /**
     * Get a user's most recent searches, newest first.
     *
     * @return Collection<int, SearchLog>
     */
    public function recentForUser(int $userId, int $limit = 8): Collection
    {
        return SearchLog::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Delete a single search log entry.
     */
    public function destroy(SearchLog $log): void
    {
        $log->delete();
    }

    /**
     * Clear all search history for a user.
     */
    public function clearForUser(int $userId): void
    {
        SearchLog::where('user_id', $userId)->delete();
    }
}
