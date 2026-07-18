<?php

namespace App\Services;

use App\Models\LotRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class BuyService
{
    /**
     * Get a base query builder for coffee requests.
     */
    public function query(): Builder
    {
        return LotRequest::query();
    }

    /**
     * Get a buyer's own submitted coffee requests, newest first.
     *
     * @return Collection<int, LotRequest>
     */
    public function requestsForUser(int $userId): Collection
    {
        return LotRequest::query()
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Get open (pending) coffee requests for sellers to discover, excluding
     * the given user's own requests.
     *
     * @return Collection<int, LotRequest>
     */
    public function openRequests(int $excludingUserId): Collection
    {
        return LotRequest::query()
            ->with('user')
            ->where('user_id', '!=', $excludingUserId)
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    /**
     * Submit a new coffee request on behalf of a buyer.
     *
     * @param  array<string, mixed>  $data
     */
    public function submitRequest(array $data, int $userId): LotRequest
    {
        return LotRequest::query()
            ->create([...$data, 'user_id' => $userId])
            ->refresh();
    }

    /**
     * Record a seller's response to a buyer's request.
     */
    public function respond(LotRequest $lotRequest, string $status, int $responderId): LotRequest
    {
        if ($lotRequest->user_id === $responderId) {
            throw ValidationException::withMessages([
                'status' => 'You cannot respond to your own request.',
            ]);
        }

        $lotRequest->update(['status' => $status]);

        return $lotRequest;
    }

    /**
     * Delete a coffee request.
     */
    public function destroy(LotRequest $lotRequest): void
    {
        $lotRequest->delete();
    }
}
