<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowAccount extends Model
{
    protected $fillable = [
        'order_id',
        'order_type',
        'buyer_id',
        'seller_id',
        'amount',
        'currency',
        'status',
        'held_at',
        'released_at',
        'released_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'held_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
