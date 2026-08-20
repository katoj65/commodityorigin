<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    /**
     * A store must be verified by an admin before its owner can open it
     * (i.e. start adding items).
     *
     * @var array<int, string>
     */
    public const VERIFICATION_STATUSES = ['pending', 'verified', 'rejected'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    /**
     * The user who owns this store.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who verified (or rejected) this store, if any.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * The items listed in this store.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StoreItem::class);
    }

    /**
     * Whether the store has been verified by an admin and may be opened.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }
}
