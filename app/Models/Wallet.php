<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'locked_balance',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'balance'        => 'decimal:2',
            'locked_balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availableBalance(): string
    {
        return bcsub($this->balance, $this->locked_balance, 2);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
