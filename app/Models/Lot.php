<?php

namespace App\Models;

use App\Helpers\QrCodeHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'variety',
        'origin',
        'region',
        'altitude',
        'year_of_harvest',
        'moisture',
        'defects_percentage',
        'screen',
        'net_weight_kg',
        'quantity_bags',
        'bag_weight_kg',
        'price',
        'currency',
        'quality_score',
        'acidity',
        'body',
        'flavor',
        'aroma',
        'balance',
        'aftertaste',
        'packaging_type',
        'status',
        'notes',
        'qr_code',
    ];

    /**
     * Bootstrap model event hooks.
     */
    protected static function booted(): void
    {
        // Every new lot gets its traceability QR code as soon as it exists —
        // the code encodes the lot's traceability URL, which needs the id
        // assigned by the insert, so it runs on `created` and is saved
        // quietly to avoid re-firing model events.
        static::created(function (Lot $lot): void {
            $lot->forceFill(['qr_code' => QrCodeHelper::forLot($lot)])->saveQuietly();
        });
    }

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
        'balance' => 'decimal:2',
        'year_of_harvest' => 'integer',
        'moisture' => 'decimal:2',
        'defects_percentage' => 'decimal:2',
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

    /**
     * Get the blockchain block this lot was committed under, if any.
     */
    public function blockchain(): HasOne
    {
        return $this->hasOne(Blockchain::class);
    }

    /**
     * Get this lot's activity log.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LotActivity::class);
    }

    /**
     * Get the market listing this lot is published under, if any.
     */
    public function market(): HasOne
    {
        return $this->hasOne(Market::class);
    }

    /**
     * Get this lot's gallery photos, up to LotImageService::MAX_IMAGES.
     */
    public function images(): HasMany
    {
        return $this->hasMany(LotImage::class)->orderBy('position');
    }

    /**
     * Get the cupping flavor notes tagged against this lot.
     */
    public function flavors(): BelongsToMany
    {
        return $this->belongsToMany(FlavorMetadata::class, 'lot_flavors');
    }
}
