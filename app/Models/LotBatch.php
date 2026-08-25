<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotBatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'lot_batch';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lot_id',
        'batch_id',
        'batch_number',
        'allocation_kg',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allocation_kg' => 'decimal:2',
    ];

    /**
     * Get the lot side of this link.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Get the batch side of this link.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the user who linked the lot to the batch.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
