<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_id',
        'user_id',
        'lot_code',
        'name',
        'origin',
        'type',
        'process',
        'quality_score',
        'quantity',
        'price_per_kg',
        'demand',
        'badges',
        'target_market',
        'status',
        'notes',
        'image',
    ];

    protected $casts = [
        'quality_score' => 'decimal:2',
        'quantity' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'badges' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
