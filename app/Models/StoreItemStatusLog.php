<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreItemStatusLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_item_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
    ];

    /**
     * The item this log entry belongs to.
     */
    public function storeItem(): BelongsTo
    {
        return $this->belongsTo(StoreItem::class);
    }

    /**
     * The user who made this status change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
