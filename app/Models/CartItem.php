<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ORDERED = 'ordered';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'market_id',
        'cartable_id',
        'cartable_type',
        'quantity',
        'unit_price',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The purchasable model this cart item points to — a coffee Market
     * listing or an AgriculturalInput. Kept alongside the legacy market()
     * relation below for anything still reading it directly.
     */
    public function cartable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @deprecated Use cartable() — kept only for the legacy market_id
     * column, which is no longer written to by new cart items.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
