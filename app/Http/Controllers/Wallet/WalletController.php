<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $wallets,
    ) {
    }

    /**
     * Display the authenticated user's wallet and transaction history.
     * Provisions an empty active wallet on first visit.
     */
    public function index(Request $request): Response
    {
        $wallet = $this->wallets->ensureForUser($request->user()->id);

        return Inertia::render('Wallet/WalletPage', [
            'wallet' => WalletResource::make($wallet)->resolve(),
            'transactions' => WalletTransactionResource::collection($this->wallets->transactionsFor($wallet))->resolve(),
        ]);
    }

    /**
     * Transfer funds from the authenticated user's wallet to another
     * platform user's wallet, by email.
     */
    public function transfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_email' => ['required', 'email'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $recipient = $this->wallets->findRecipient($validated['recipient_email']);

        if (! $recipient) {
            throw ValidationException::withMessages(['recipient_email' => 'No user was found with that email.']);
        }

        $this->wallets->transfer(
            $request->user()->id,
            $recipient->id,
            (string) $validated['amount'],
            $validated['description'] ?? null,
        );

        return back()->with('success', 'Transfer sent.');
    }

    /**
     * Withdraw funds from the authenticated user's wallet.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $this->wallets->withdraw(
            $request->user()->id,
            (string) $validated['amount'],
            $validated['description'] ?? null,
        );

        return back()->with('success', 'Withdrawal complete.');
    }
}
