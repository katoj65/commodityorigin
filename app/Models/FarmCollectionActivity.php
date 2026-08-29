<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmCollectionActivity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'farm_collection_id',
        'user_id',
        'event',
        'description',
    ];

    /**
     * Get the farm collection this activity was recorded for.
     */
    public function farmCollection(): BelongsTo
    {
        return $this->belongsTo(FarmCollection::class);
    }

    /**
     * Get the user who performed this activity, if known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
