<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Extra gallery photos for this listing (up to 3, enforced in
     * MarketImageService) — separate from the single `image` cover field.
     */
    public function images(): HasMany
    {
        return $this->hasMany(MarketImage::class)->orderBy('position');
    }
}
