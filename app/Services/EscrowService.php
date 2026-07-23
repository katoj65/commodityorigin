<?php

namespace App\Services;

use App\Models\EscrowAccount;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EscrowService
{
    public function __construct(
        private readonly WalletService $wallets,
    ) {
    }

    /**
     * Get a base query builder for escrow accounts.
     */
    public function query(): Builder
    {
        return EscrowAccount::query();
    }

    /**
     * Get every escrow account an admin can see, or that a buyer/seller is
     * party to, newest first. Admins see the whole ledger; everyone else
     * only sees escrow accounts tied to orders they're part of.
     *
     * @return Collection<int, EscrowAccount>
     */
    public function forUser(int $userId, bool $isAdmin = false): Collection
    {
        return EscrowAccount::query()
            ->with(['order', 'buyer', 'seller', 'releasedBy'])
            ->when(! $isAdmin, fn (Builder $query) => $query->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->latest()
            ->get();
    }

    /**
     * Hold the buyer's funds in escrow and immediately release them into
     * the seller's wallet. Triggered when an admin activates shipping on a
     * processing order — the point at which the trade is confirmed and
     * payment is due. Debits the buyer's wallet, credits the seller's, and
     * leaves a ledger entry recording both the hold and the release.
     */
    public function holdAndRelease(Order $order, int $adminId): EscrowAccount
    {
        return DB::transaction(function () use ($order, $adminId) {
            $buyerWallet = Wallet::where('user_id', $order->buyer_id)->lockForUpdate()->first();

            if (! $buyerWallet || ! $buyerWallet->isActive()) {
                throw ValidationException::withMessages(['escrow' => "The buyer doesn't have an active wallet."]);
            }

            if (bccomp($buyerWallet->availableBalance(), (string) $order->total_amount, 2) < 0) {
                throw ValidationException::withMessages(['escrow' => "The buyer's wallet balance is insufficient to fund this order."]);
            }

            $sellerWallet = Wallet::where('user_id', $order->seller_id)->lockForUpdate()->first();

            if (! $sellerWallet || ! $sellerWallet->isActive()) {
                throw ValidationException::withMessages(['escrow' => "The seller doesn't have an active wallet."]);
            }

            $now = now();

            $escrow = EscrowAccount::create([
                'order_id' => $order->id,
                'order_type' => $order->type,
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => 'held',
                'held_at' => $now,
            ]);

            $this->wallets->debit(
                $buyerWallet,
                (string) $order->total_amount,
                'escrow_hold',
                "Escrow hold for order {$order->order_number}",
                $order->order_number,
                $sellerWallet->id,
            );

            $this->wallets->credit(
                $sellerWallet,
                (string) $order->total_amount,
                'escrow_release',
                "Payment received for order {$order->order_number}",
                $order->order_number,
                $buyerWallet->id,
            );

            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
                'released_by' => $adminId,
            ]);

            return $escrow;
        });
    }
}
