<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowWalletTransaction extends Model
{
    protected $fillable = [
        'escrow_wallet_id',
        'type',
        'amount',
        'currency',
        'balance_after',
        'description',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function escrowWallet(): BelongsTo
    {
        return $this->belongsTo(EscrowWallet::class);
    }
}
