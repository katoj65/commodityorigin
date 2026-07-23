<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderInspection extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_inspections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'requested_by',
        'status',
        'buyer_acknowledged_at',
        'completed_by',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'buyer_acknowledged_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * The order this inspection was requested on.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The seller who requested this inspection.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * The admin who confirmed this inspection as complete.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
