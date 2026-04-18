<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'farm_id',
        'variety',
        'date_planted',
        'harvest_date',
        'harvest_season',
        'pick_method',
        'price',
        'weight',
        'ripeness_percentage',
        'foreign_matter_present',
        'pest_damage',
        'disease_signs',
        'visible_defects',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_planted' => 'date',
        'harvest_date' => 'date',
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'ripeness_percentage' => 'decimal:2',
        'foreign_matter_present' => 'boolean',
        'pest_damage' => 'boolean',
        'disease_signs' => 'boolean',
        'visible_defects' => 'boolean',
    ];

    /**
     * Get the farm attached to this harvest.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Get the user who created the harvest record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
