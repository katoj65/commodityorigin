<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'farmer_id',
        'name',
        'location',
        'size',
        'altitude',
        'variety',
        'latitude',
        'longitude',
        'status',
        'notes',
    ];

    /**
     * Get the farmer that owns the farm.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get the lots attached to the farm.
     */
    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}
