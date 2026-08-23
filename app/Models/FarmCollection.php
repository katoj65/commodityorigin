<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmCollection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'farm_id',
        'collection_date',
        'coffee_type',
        'variety',
        'harvest_season',
        'quantity',
        'unit',
        'initial_moisture',
        'initial_defects',
        'initial_grade',
        'initial_quality_score',
        'collection_price',
        'currency',
        'payment_status',
        'reference',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'collection_date' => 'date',
        'quantity' => 'decimal:2',
        'initial_moisture' => 'decimal:2',
        'initial_defects' => 'decimal:2',
        'initial_quality_score' => 'decimal:2',
        'collection_price' => 'decimal:2',
    ];

    /**
     * Get the farm this collection was recorded against.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
