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
        'blockchain_id',
        'user_id',
        'title',
        'description',
        'quantity',
        'available_quantity',
        'unit',
        'currency',
        'price_per_unit',
        'pricing_type',
        'minimum_order_quantity',
        'payment_terms',
        'delivery_terms',
        'delivery_location',
        'status',
        'is_featured',
        'is_public',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'minimum_order_quantity' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function blockchain(): BelongsTo
    {
        return $this->belongsTo(Blockchain::class);
    }

    /**
     * Extra gallery photos for this listing (up to 3, enforced in
     * MarketImageService) — separate from the cover image kept in
     * metadata['image'].
     */
    public function images(): HasMany
    {
        return $this->hasMany(MarketImage::class)->orderBy('position');
    }

    /*
    |--------------------------------------------------------------------
    | Metadata-backed virtual attributes
    |--------------------------------------------------------------------
    | The columns markets had before this schema was restructured to a
    | general commodity-listing shape — origin, type, process,
    | quality_score, demand, badges, target_market, image — have no direct
    | column equivalent now, but MarketService's filtering/analytics and
    | the listing edit form still read/write them by these names. These
    | accessors keep that code working by reading through to `metadata`
    | instead; write paths must set `metadata` explicitly (these are
    | read-only — there's no matching column for mass assignment to hit).
    */

    public function getLotCodeAttribute(): ?string
    {
        return $this->lot?->lot_number ?? $this->metadata['lot_code'] ?? null;
    }

    public function getOriginAttribute(): ?string
    {
        return $this->metadata['origin'] ?? null;
    }

    public function getTypeAttribute(): ?string
    {
        return $this->metadata['type'] ?? null;
    }

    public function getProcessAttribute(): ?string
    {
        return $this->metadata['process'] ?? null;
    }

    public function getQualityScoreAttribute(): ?float
    {
        $value = $this->metadata['quality_score'] ?? null;

        return $value === null ? null : (float) $value;
    }

    public function getDemandAttribute(): ?string
    {
        return $this->metadata['demand'] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function getBadgesAttribute(): array
    {
        return $this->metadata['badges'] ?? [];
    }

    public function getTargetMarketAttribute(): ?string
    {
        return $this->metadata['target_market'] ?? null;
    }

    /**
     * The listing's cover photo — the real lot's own cover image (or, if
     * it has none of those but does have gallery photos, the first one)
     * takes priority, so a lot-backed listing shows a real coffee photo
     * instead of the old generic metadata placeholder. Falls back to
     * metadata['image'] only when there's no lot, or the lot has no
     * photos of its own at all.
     */
    public function getImageAttribute(): ?string
    {
        return $this->lot?->image
            ?? $this->lot?->images?->first()?->image
            ?? $this->metadata['image'] ?? null;
    }
}
