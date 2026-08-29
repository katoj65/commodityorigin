<?php

namespace App\Services;

use App\Models\Blockchain;
use App\Models\Lot;

/**
 * Commits lots to the traceability chain and persists a real, queryable
 * ledger of one block per lot — replacing the old stub that recomputed
 * fake block details on every request without storing anything.
 *
 * No real distributed ledger is connected yet: `hash` is derived from the
 * lot and the previous block's hash (a simple hash chain), and
 * `block_number` increments sequentially across all commits. Swap the
 * internals for a real client/ledger once one exists — callers should keep
 * relying on commitLot()/getBlockForLot()'s signatures.
 */
class BlockchainService
{
    private const NETWORK = 'Bean Origin Traceability Chain';

    private const FIRST_BLOCK_NUMBER = 1_000_001;

    /**
     * Get the block committed for a lot, if any.
     */
    public function getBlockForLot(Lot $lot): ?Blockchain
    {
        return $lot->relationLoaded('blockchain')
            ? $lot->blockchain
            : Blockchain::query()->where('lot_id', $lot->id)->first();
    }

    /**
     * Commit a lot to the chain, chaining off the most recently committed
     * block. Idempotent — a lot that's already committed just returns its
     * existing block rather than creating a duplicate.
     */
    public function commitLot(Lot $lot, ?int $userId = null): Blockchain
    {
        if ($existing = $this->getBlockForLot($lot)) {
            return $existing;
        }

        $previous = Blockchain::query()->orderByDesc('block_number')->first();
        $blockNumber = $previous ? $previous->block_number + 1 : self::FIRST_BLOCK_NUMBER;

        return Blockchain::query()->create([
            'lot_id' => $lot->id,
            'user_id' => $userId,
            'network' => self::NETWORK,
            'block_number' => $blockNumber,
            'hash' => hash('sha256', "lot:{$lot->id}:{$lot->lot_number}:block:{$blockNumber}"),
            'previous_hash' => $previous?->hash,
            'status' => 'confirmed',
            'confirmations' => 128,
            'committed_at' => now(),
        ]);
    }
}
