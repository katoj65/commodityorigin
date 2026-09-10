<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketAlert extends Model
{
    use HasFactory;

    protected $table = 'market_alerts';

    /**
     * The alert types a user can toggle, keyed by their stored `type`
     * value, mapped to the label shown in the Market Alerts panel.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'price_drop' => 'Price Drop Alerts',
        'new_specialty_lots' => 'New Specialty Lots',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    /**
     * The user this alert preference belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
