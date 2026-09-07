<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmOwner extends Model
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
        'first_name',
        'middle_name',
        'last_name',
        'national_id',
        'tel',
        'email',
        'ownership_percentage',
        'is_primary',
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    protected $appends = [
        'name',
    ];

    /**
     * Get the owner's display name.
     */
    protected function name(): Attribute
    {
        return Attribute::get(function (mixed $value, array $attributes): string {
            return trim(
                implode(' ', array_filter([
                    $attributes['first_name'] ?? null,
                    $attributes['middle_name'] ?? null,
                    $attributes['last_name'] ?? null,
                ]))
            );
        });
    }

    /**
     * Get the farm this owner record belongs to.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Get the user who recorded this owner entry, if known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
