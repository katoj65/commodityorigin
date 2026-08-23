<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketImage extends Model
{
    protected $fillable = [
        'market_id',
        'image',
        'position',
    ];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }
}
