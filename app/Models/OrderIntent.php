<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderIntent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_intent';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'status',
    ];

    /**
     * The order this intent was expressed on.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The user who expressed this intent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
