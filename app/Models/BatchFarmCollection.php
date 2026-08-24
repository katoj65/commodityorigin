<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchFarmCollection extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'batch_farm_collection';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'batch_id',
        'farm_collection_id',
        'user_id',
        'status',
    ];

    /**
     * Get the batch this link belongs to.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the farm collection this link belongs to.
     */
    public function farmCollection(): BelongsTo
    {
        return $this->belongsTo(FarmCollection::class);
    }

    /**
     * Get the user who created this link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
