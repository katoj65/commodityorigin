<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotSensoryProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lot_id',
        'aroma_score',
        'acidity_score',
        'body_score',
        'aftertaste_score',
        'flavor_notes',
        'fragrance_notes',
        'cupping_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aroma_score' => 'decimal:2',
            'acidity_score' => 'decimal:2',
            'body_score' => 'decimal:2',
            'aftertaste_score' => 'decimal:2',
        ];
    }

    /**
     * Get the lot that owns the sensory profile.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
