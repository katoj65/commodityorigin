<?php

namespace App\Services;

use App\Models\Lot;

/**
 * Stub blockchain service — no real chain is connected yet. Block details
 * are deterministically derived from the lot's own id/number so the same
 * lot always returns the same "block" across requests, without persisting
 * anything. Swap the internals for a real client/ledger once one exists;
 * the public method signature is the contract callers should rely on.
 */
class BlockchainService
{
    /**
     * Get the block details associated with a lot.
     *
     * @return array<string, mixed>
     */
    public function getBlockForLot(Lot $lot): array
    {
        $seed = "lot:{$lot->id}:{$lot->lot_number}";

        return [
            'lot_id' => $lot->id,
            'network' => 'Bean Origin Traceability Chain',
            'block_number' => 1_000_000 + $lot->id,
            'hash' => hash('sha256', $seed),
            'previous_hash' => hash('sha256', 'block:' . ($lot->id - 1)),
            'timestamp' => optional($lot->created_at)->toIso8601String(),
            'confirmations' => 128,
            'status' => 'confirmed',
        ];
    }
}
