<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'batch_number',
        'variety',
        'warehouse_location',
        'quantity',
        'weight',
        'price',
        'moisture_content',
        'processing_date',
        'processing_method',
        'drying_method',
        'drying_duration',
        'milling_status',
        'screen_size',
        'defect_count',
        'cup_score',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'processing_date' => 'date',
        'weight' => 'decimal:2',
        'price' => 'decimal:2',
        'moisture_content' => 'decimal:2',
        'drying_duration' => 'integer',
        'defect_count' => 'integer',
        'cup_score' => 'decimal:2',
    ];

    /**
     * Get the ownership records attached to the batch.
     */
    public function ownerships(): HasMany
    {
        return $this->hasMany(BatchOwnership::class);
    }

    /**
     * Get the compliance records attached to the batch.
     */
    public function compliances(): HasMany
    {
        return $this->hasMany(BatchCompliance::class);
    }
}
