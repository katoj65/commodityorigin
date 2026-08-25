<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'user_id',
        'lot_number',
        'lot_name',
        'description',
        'image',
        'process',
        'grade',
        'net_weight_kg',
        'quantity_bags',
        'bag_weight_kg',
        'price',
        'quality_score',
        'packaging_type',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'net_weight_kg' => 'decimal:2',
        'bag_weight_kg' => 'decimal:2',
        'price' => 'decimal:2',
        'quality_score' => 'decimal:2',
    ];

    /**
     * Get the batch links for this lot, via the lot_batch pivot table.
     */
    public function lotBatches(): HasMany
    {
        return $this->hasMany(LotBatch::class);
    }

    /**
     * Convenience read accessor for the batch this lot was primarily
     * sourced from — the first batch linked via the lot_batch pivot table.
     * Uses the already eager-loaded `lotBatches` relation when available
     * (including any of its own nested eager loads) to avoid N+1 queries;
     * only falls back to a fresh query when lotBatches wasn't loaded.
     */
    public function getBatchAttribute(): ?Batch
    {
        if ($this->relationLoaded('lotBatches')) {
            return $this->lotBatches->first()?->batch;
        }

        return $this->lotBatches()->with('batch')->first()?->batch;
    }

    /**
     * Get the user that created this lot.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
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
