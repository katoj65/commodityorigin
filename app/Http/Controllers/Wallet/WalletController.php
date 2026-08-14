<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Resources\EscrowWalletResource;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Services\EscrowWalletService;
use App\Services\NotificationService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly EscrowWalletService $escrowWallets,
        private readonly NotificationService $notifications,
    ) {
    }

    /**
     * Display the authenticated user's wallet and transaction history.
     * Provisions an empty active wallet on first visit.
     */
    public function index(Request $request): Response
    {
        $wallet = $this->wallets->ensureForUser($request->user()->id);
        $escrowWallet = $this->escrowWallets->ensureForUser($request->user()->id, $wallet->currency);

        return Inertia::render('Wallet/WalletPage', [
            'wallet' => WalletResource::make($wallet)->resolve(),
            'escrowWallet' => EscrowWalletResource::make($escrowWallet)->resolve(),
            'transactions' => WalletTransactionResource::collection($this->wallets->transactionsFor($wallet))->resolve(),
        ]);
    }

    /**
     * Move funds from the authenticated user's wallet into their escrow
     * wallet. The money stays held there until the user spends it on a
     * transaction — it isn't sent to another user.
     */
    public function transfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $escrowWallet = $this->escrowWallets->fund(
            $request->user()->id,
            (string) $validated['amount'],
            $validated['description'] ?? null,
        );

        $this->notifications->notify(
            userId: $request->user()->id,
            type: 'wallet.transfer',
            category: 'wallet',
            title: 'Funds moved to escrow',
            body: sprintf('%s %s was transferred from your wallet to escrow.', $escrowWallet->currency, number_format((float) $validated['amount'], 2)),
            priority: 'normal',
            actionUrl: route('wallet.index'),
            data: ['amount' => $validated['amount'], 'currency' => $escrowWallet->currency],
            source: $escrowWallet,
        );

        return back()->with('success', 'Transferred to escrow.');
    }

    /**
     * Deposit funds into the authenticated user's wallet, by card or mobile
     * money. There's no payment gateway wired up, so this simply credits
     * the wallet directly and records which method was used for the
     * ledger — card numbers/CVVs are never sent here, only a masked
     * last4/brand/expiry.
     */
    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['card', 'mobile_money'])],
            'card' => ['required_if:payment_method,card', 'nullable', 'array'],
            'card.holder' => ['required_if:payment_method,card', 'nullable', 'string', 'max:255'],
            'card.last4' => ['required_if:payment_method,card', 'nullable', 'digits:4'],
            'card.brand' => ['nullable', 'string', 'max:30'],
            'card.expiry' => ['required_if:payment_method,card', 'nullable', 'string', 'max:5'],
            'mobile_money' => ['required_if:payment_method,mobile_money', 'nullable', 'array'],
            'mobile_money.provider' => ['required_if:payment_method,mobile_money', 'nullable', 'string', 'max:60'],
            'mobile_money.phone' => ['required_if:payment_method,mobile_money', 'nullable', 'string', 'max:20'],
        ]);

        $this->wallets->deposit(
            $request->user()->id,
            (string) $validated['amount'],
            $this->buildDepositDescription($validated),
        );

        return back()->with('success', 'Deposit complete.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function buildDepositDescription(array $validated): string
    {
        if ($validated['payment_method'] === 'mobile_money') {
            return "Deposit via {$validated['mobile_money']['provider']} ({$validated['mobile_money']['phone']})";
        }

        return "Deposit via card ending {$validated['card']['last4']}";
    }

    /**
     * Withdraw funds from the authenticated user's wallet, by bank account
     * or mobile money transfer. There's no payout gateway wired up, so this
     * simply debits the wallet directly and records which method was used
     * for the ledger — the full bank account number is never sent here,
     * only a masked last4 plus bank name and account holder.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['bank', 'mobile_money'])],
            'bank' => ['required_if:payment_method,bank', 'nullable', 'array'],
            'bank.bank_name' => ['required_if:payment_method,bank', 'nullable', 'string', 'max:255'],
            'bank.account_holder' => ['required_if:payment_method,bank', 'nullable', 'string', 'max:255'],
            'bank.last4' => ['required_if:payment_method,bank', 'nullable', 'digits:4'],
            'mobile_money' => ['required_if:payment_method,mobile_money', 'nullable', 'array'],
            'mobile_money.provider' => ['required_if:payment_method,mobile_money', 'nullable', 'string', 'max:60'],
            'mobile_money.phone' => ['required_if:payment_method,mobile_money', 'nullable', 'string', 'max:20'],
        ]);

        $this->wallets->withdraw(
            $request->user()->id,
            (string) $validated['amount'],
            $this->buildWithdrawDescription($validated),
        );

        return back()->with('success', 'Withdrawal complete.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function buildWithdrawDescription(array $validated): string
    {
        if ($validated['payment_method'] === 'mobile_money') {
            return "Withdrawal via {$validated['mobile_money']['provider']} ({$validated['mobile_money']['phone']})";
        }

        return "Withdrawal to {$validated['bank']['bank_name']} account ending {$validated['bank']['last4']}";
    }
}
