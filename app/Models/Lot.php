<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'batch_id',
        'user_id',
        'lot_number',
        'lot_name',
        'process',
        'grade',
        'allocation_kg',
        'net_weight_kg',
        'quantity_bags',
        'bag_weight_kg',
        'reserve_price',
        'quality_score',
        'warehouse',
        'packaging_type',
        'screen_size',
        'altitude',
        'aroma_score',
        'acidity_score',
        'body_score',
        'target_market',
        'price_per_kg',
        'tokenize',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allocation_kg' => 'decimal:2',
        'net_weight_kg' => 'decimal:2',
        'bag_weight_kg' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'aroma_score' => 'decimal:2',
        'acidity_score' => 'decimal:2',
        'body_score' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'tokenize' => 'boolean',
    ];

    /**
     * Get the batch this lot was created from.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the user that created this lot.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sensory profile associated with the lot.
     */
    public function sensoryProfile(): HasOne
    {
        return $this->hasOne(LotSensoryProfile::class);
    }

    /**
     * Get the storage profile associated with the lot.
     */
    public function storageProfile(): HasOne
    {
        return $this->hasOne(LotStorageProfile::class);
    }
}
