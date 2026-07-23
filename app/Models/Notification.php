<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'category',
        'priority',
        'title',
        'body',
        'action_url',
        'icon',
        'data',
        'source_type',
        'source_id',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * The user this notification was sent to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The domain entity that triggered this notification, if any.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only unread notifications.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to a given category.
     */
    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
