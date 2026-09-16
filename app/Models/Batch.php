<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Batch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'batch_number',
        'variety',
        'warehouse_location',
        'quantity',
        'weight',
        'price',
        'currency',
        'moisture_content',
        'processing_date',
        'processing_method',
        'drying_method',
        'drying_duration',
        'milling_status',
        'screen_size',
        'defect_count',
        'cup_score',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'processing_date' => 'date',
        'weight' => 'decimal:2',
        'price' => 'decimal:2',
        'moisture_content' => 'decimal:2',
        'drying_duration' => 'integer',
        'defect_count' => 'integer',
        'cup_score' => 'decimal:2',
    ];

    /**
     * Get the user who recorded this batch.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lots linked to this batch, via the lot_batch pivot table.
     */
    public function lotBatches(): HasMany
    {
        return $this->hasMany(LotBatch::class);
    }

    /**
     * Get the farm collections linked to this batch, via the
     * batch_farm_collection pivot table.
     */
    public function batchFarmCollections(): HasMany
    {
        return $this->hasMany(BatchFarmCollection::class);
    }

    /**
     * Get this batch's activity log, via BatchActivityService.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(BatchActivity::class);
    }

    /**
     * Get this batch's bonded-warehousing/storage record, if recorded.
     */
    public function warehouse(): MorphOne
    {
        return $this->morphOne(Warehouse::class, 'item');
    }

    /**
     * Get this batch's dedicated storage record, if recorded. Distinct
     * from warehouse() (Warehouse) — that's the shared polymorphic
     * warehousing table also used by farm collections and lots; this is a
     * batch-only storage record keyed directly on batch_id.
     */
    public function batchStorage(): HasOne
    {
        return $this->hasOne(BatchStorage::class);
    }
}
