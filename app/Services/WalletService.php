<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Get a base query builder for wallets.
     */
    public function query(): Builder
    {
        return Wallet::query();
    }

    /**
     * Get a user's wallet, if they have one.
     */
    public function forUser(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    /**
     * Get a user's wallet, creating an empty active one if they don't
     * have one yet — so visiting the wallet page or receiving a transfer
     * never blocks on manual provisioning.
     */
    public function ensureForUser(int $userId, string $currency = 'USD'): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'locked_balance' => 0, 'currency' => $currency, 'status' => 'active']
        );
    }

    /**
     * Update a wallet's attributes (e.g. status).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Wallet $wallet, array $data): Wallet
    {
        $wallet->update($data);

        return $wallet;
    }

    /**
     * Delete a wallet.
     */
    public function destroy(Wallet $wallet): void
    {
        $wallet->delete();
    }

    /**
     * Get a wallet's transaction history, newest first.
     *
     * @return Collection<int, WalletTransaction>
     */
    public function transactionsFor(Wallet $wallet): Collection
    {
        return $wallet->transactions()->with('counterpartyWallet.user')->latest()->get();
    }

    /**
     * Debit a wallet and record the ledger entry. Fails if the wallet
     * isn't active or doesn't have enough available balance.
     */
    public function debit(
        Wallet $wallet,
        string $amount,
        string $type,
        ?string $description = null,
        ?string $reference = null,
        ?int $counterpartyWalletId = null,
    ): WalletTransaction {
        if (! $wallet->isActive()) {
            throw ValidationException::withMessages(['wallet' => 'This wallet is not active.']);
        }

        if (bccomp($wallet->availableBalance(), $amount, 2) < 0) {
            throw ValidationException::withMessages(['wallet' => 'Insufficient wallet balance.']);
        }

        $wallet->decrement('balance', $amount);

        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'amount' => $amount,
            'currency' => $wallet->currency,
            'balance_after' => $wallet->fresh()->balance,
            'description' => $description,
            'reference' => $reference,
            'counterparty_wallet_id' => $counterpartyWalletId,
        ]);
    }

    /**
     * Credit a wallet and record the ledger entry. Fails if the wallet
     * isn't active.
     */
    public function credit(
        Wallet $wallet,
        string $amount,
        string $type,
        ?string $description = null,
        ?string $reference = null,
        ?int $counterpartyWalletId = null,
    ): WalletTransaction {
        if (! $wallet->isActive()) {
            throw ValidationException::withMessages(['wallet' => 'This wallet is not active.']);
        }

        $wallet->increment('balance', $amount);

        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'amount' => $amount,
            'currency' => $wallet->currency,
            'balance_after' => $wallet->fresh()->balance,
            'description' => $description,
            'reference' => $reference,
            'counterparty_wallet_id' => $counterpartyWalletId,
        ]);
    }

    /**
     * Transfer funds between two users' wallets, atomically, with a
     * matching ledger entry on both sides.
     *
     * @return array{from: Wallet, to: Wallet}
     */
    public function transfer(int $fromUserId, int $toUserId, string $amount, ?string $description = null): array
    {
        if ($fromUserId === $toUserId) {
            throw ValidationException::withMessages(['recipient' => 'You cannot transfer to yourself.']);
        }

        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Enter a valid amount.']);
        }

        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $description) {
            $fromWallet = Wallet::where('user_id', $fromUserId)->lockForUpdate()->first();

            if (! $fromWallet) {
                throw ValidationException::withMessages(['wallet' => "You don't have a wallet yet."]);
            }

            $toWallet = Wallet::where('user_id', $toUserId)->lockForUpdate()->first();

            if (! $toWallet) {
                throw ValidationException::withMessages(['recipient' => "That user doesn't have a wallet yet."]);
            }

            $reference = 'TRF-'.now()->format('YmdHis').'-'.$fromWallet->id;

            $this->debit($fromWallet, $amount, 'transfer_out', $description, $reference, $toWallet->id);
            $this->credit($toWallet, $amount, 'transfer_in', $description, $reference, $fromWallet->id);

            return ['from' => $fromWallet->fresh(), 'to' => $toWallet->fresh()];
        });
    }

    /**
     * Find the user a transfer should go to, by email.
     */
    public function findRecipient(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Withdraw funds out of a user's wallet, logging the ledger entry.
     */
    public function withdraw(int $userId, string $amount, ?string $description = null): Wallet
    {
        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Enter a valid amount.']);
        }

        return DB::transaction(function () use ($userId, $amount, $description) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

            if (! $wallet) {
                throw ValidationException::withMessages(['wallet' => "You don't have a wallet yet."]);
            }

            $reference = 'WDR-'.now()->format('YmdHis').'-'.$wallet->id;

            $this->debit($wallet, $amount, 'withdrawal', $description, $reference);

            return $wallet->fresh();
        });
    }
}
