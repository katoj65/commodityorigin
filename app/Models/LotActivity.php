<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotActivity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lot_id',
        'user_id',
        'event',
        'description',
    ];

    /**
     * Get the lot this activity was recorded for.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Get the user who performed this activity, if known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
