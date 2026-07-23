<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'currency',
        'balance_after',
        'description',
        'counterparty_wallet_id',
        'reference',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function counterpartyWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'counterparty_wallet_id');
    }

    public function isCredit(): bool
    {
        return in_array($this->type, ['deposit', 'transfer_in', 'escrow_release'], true);
    }
}
