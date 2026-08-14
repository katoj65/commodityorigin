<?php

namespace App\Services;

use App\Models\EscrowWallet;
use App\Models\EscrowWalletTransaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EscrowWalletService
{
    public function __construct(
        private readonly WalletService $wallets,
    ) {
    }

    /**
     * Get a user's escrow wallet, creating an empty one if they don't have
     * one yet.
     */
    public function ensureForUser(int $userId, string $currency = 'USD'): EscrowWallet
    {
        return EscrowWallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'currency' => $currency]
        );
    }

    /**
     * Move funds from a user's wallet into their escrow wallet. The money
     * stays held there until the user spends it on a transaction — it is
     * not tied to any specific order at the point it's funded.
     */
    public function fund(int $userId, string $amount, ?string $description = null): EscrowWallet
    {
        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Enter a valid amount.']);
        }

        return DB::transaction(function () use ($userId, $amount, $description) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

            if (! $wallet) {
                throw ValidationException::withMessages(['wallet' => "You don't have a wallet yet."]);
            }

            $escrowWallet = $this->ensureForUser($userId, $wallet->currency);
            $escrowWallet = EscrowWallet::where('id', $escrowWallet->id)->lockForUpdate()->first();

            $reference = 'ESC-'.now()->format('YmdHis').'-'.$wallet->id;

            $this->wallets->debit($wallet, $amount, 'escrow_fund', $description ?? 'Transferred to escrow', $reference);

            $escrowWallet->increment('balance', $amount);

            EscrowWalletTransaction::create([
                'escrow_wallet_id' => $escrowWallet->id,
                'type' => 'funded',
                'amount' => $amount,
                'currency' => $escrowWallet->currency,
                'balance_after' => $escrowWallet->fresh()->balance,
                'description' => $description ?? 'Funded from wallet',
                'reference' => $reference,
            ]);

            return $escrowWallet->fresh();
        });
    }
}
