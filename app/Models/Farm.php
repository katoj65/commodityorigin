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
        'total_bags_produced',
        'temperature',
        'rainfall',
        'humidity',
        'soil_type',
        'climatic_zone',
    ];

    protected $casts = [
        'total_bags_produced' => 'integer',
        'latitude'            => 'float',
        'longitude'           => 'float',
    ];

    /**
     * Get the farmer that owns the farm.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get the harvest records attached to this farm.
     */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }
}
