<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'type',
        'buyer_id',
        'seller_id',
        'crop_type',
        'variety',
        'grade',
        'quantity',
        'unit_price',
        'total_amount',
        'currency',
        'payment_method',
        'notes',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * The buyer who placed this order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * The seller who is fulfilling this order.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Every expression of intent submitted on this order.
     */
    public function intents(): HasMany
    {
        return $this->hasMany(OrderIntent::class);
    }

    /**
     * Every inspection requested on this order.
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(OrderInspection::class);
    }

    /**
     * The order's current (most recent) inspection, if any.
     */
    public function inspection(): HasOne
    {
        return $this->hasOne(OrderInspection::class)->latestOfMany();
    }
}
