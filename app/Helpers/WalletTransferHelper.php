<?php

namespace App\Helpers;

use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletTransferHelper
{
    /**
     * Transfer an amount from one wallet to another atomically. Debits the
     * sender and credits the recipient, recording matching ledger entries on
     * both sides.
     */
    public static function transfer(
        Wallet $from,
        Wallet $to,
        string $amount,
        ?string $reference = null,
        ?string $description = null,
    ): void {
        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Enter a valid amount.']);
        }

        if ($from->id === $to->id) {
            throw ValidationException::withMessages(['wallet' => 'Cannot transfer to the same wallet.']);
        }

        $reference ??= 'TRF-' . now()->format('YmdHis') . '-' . $from->id;

        DB::transaction(function () use ($from, $to, $amount, $reference, $description) {
            $service = app(WalletService::class);

            $from = Wallet::whereKey($from->id)->lockForUpdate()->first();
            $to = Wallet::whereKey($to->id)->lockForUpdate()->first();

            if (! $from || ! $to) {
                throw ValidationException::withMessages(['wallet' => 'Wallet not found.']);
            }

            $service->debit($from, $amount, 'transfer_out', $description, $reference, $to->id);
            $service->credit($to, $amount, 'transfer_in', $description, $reference, $from->id);
        });
    }
}
