<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'offer_id',
        'user_id',
        'order_owner_id',
        'message',
        'status',
    ];

    /**
     * The offer this response was submitted against.
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * The user (buyer) who submitted this response — the responder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The owner of the offer — the seller who originally requested interest.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'order_owner_id');
    }
}
