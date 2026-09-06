<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Email extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'recipient',
        'subject',
        'body',
        'action_text',
        'action_url',
        'status',
        'error',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * The user this email was addressed to, when the recipient is a known
     * platform account rather than a bare email address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
