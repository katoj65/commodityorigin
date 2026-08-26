<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'user_id',
        'name',
        'farm_code',
        'country',
        'region',
        'district',
        'county',
        'subcounty',
        'parish',
        'village',
        'latitude',
        'longitude',
        'elevation',
        'total_area',
        'coffee_area',
        'coffee_type',
        'tel',
        'email',
        'status',
        'soil_metadata_id',
        'climate_zone_metadata_id',
        'water_conservation_percentage',
        'carbon_sequestration',
        'soil_health_index',
        'soil_type',
    ];

    protected $casts = [
        'latitude'    => 'float',
        'longitude'   => 'float',
        'elevation'   => 'float',
        'total_area'  => 'float',
        'coffee_area' => 'float',
        'water_conservation_percentage' => 'float',
        'carbon_sequestration' => 'float',
        'soil_health_index' => 'float',
    ];

    /**
     * Get the user who owns this farm.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the documents uploaded against this farm.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(FarmDocument::class);
    }

    /**
     * Get the coffee collections recorded against this farm.
     */
    public function collections(): HasMany
    {
        return $this->hasMany(FarmCollection::class);
    }

    /**
     * The farm's soil type, if recorded.
     */
    public function soil(): BelongsTo
    {
        return $this->belongsTo(SoilMetadata::class, 'soil_metadata_id');
    }

    /**
     * The farm's climate zone, if recorded.
     */
    public function climateZone(): BelongsTo
    {
        return $this->belongsTo(ClimateZoneMetadata::class, 'climate_zone_metadata_id');
    }

    /**
     * The crop varieties grown on this farm.
     */
    public function cropVarieties(): BelongsToMany
    {
        return $this->belongsToMany(CropVarietyMetadata::class, 'farm_crop_varieties');
    }

    /**
     * The certifications this farm holds.
     */
    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(CertificationMetadata::class, 'farm_certifications');
    }

    /**
     * The farmers linked to this farm, via the farmers_farms pivot table.
     */
    public function farmers(): BelongsToMany
    {
        return $this->belongsToMany(Farmer::class, 'farmers_farms')
            ->withPivot(['farm_code', 'status'])
            ->withTimestamps();
    }
}
