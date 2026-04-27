<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestSustainability extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'harvest_id',
        'user_id',
        'organic_certified',
        'climate_smart',
        'shade_grown',
        'water_management',
        'soil_conservation',
        'low_carbon',
        'fair_wages',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'organic_certified' => 'boolean',
        'climate_smart' => 'boolean',
        'shade_grown' => 'boolean',
        'water_management' => 'boolean',
        'soil_conservation' => 'boolean',
        'low_carbon' => 'boolean',
        'fair_wages' => 'boolean',
    ];

    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
