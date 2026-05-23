<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = ['lot_id', 'user_id', 'bid_amount', 'quantity', 'notes', 'status'];

    protected $casts = [
        'bid_amount' => 'decimal:2',
        'quantity'   => 'decimal:2',
    ];

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
