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
        'farm_id',
        'variety',
        'date_planted',
        'harvest_date',
        'pick_method',
        'price',
        'weight',
        'ripeness_grade',
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
    ];

    /**
     * Get the farm attached to this harvest.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
