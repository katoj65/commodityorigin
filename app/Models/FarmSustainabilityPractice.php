<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmSustainabilityPractice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'farm_id',
        'user_id',
        'practice',
        'description',
    ];

    /**
     * Get the farm this practice was recorded for.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Get the user who recorded this practice, if known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
