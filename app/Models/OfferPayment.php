<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'offer_id',
        'offer_response_id',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'reference',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * The offer this payment settles.
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * The response this payment is linked to, if any.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(OfferResponse::class, 'offer_response_id');
    }

    /**
     * The buyer who made this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
