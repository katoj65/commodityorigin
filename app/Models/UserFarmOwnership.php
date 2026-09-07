<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFarmOwnership extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_farm_ownership';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'farm_id',
        'user_id',
        'ownership_percentage',
        'is_primary',
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    /**
     * The farm this ownership record belongs to.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * The user who owns the farm.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
