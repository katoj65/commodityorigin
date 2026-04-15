<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'batch_number',
        'warehouse_location',
        'quantity',
        'weight',
        'moisture_content',
        'status',
        'notes',
    ];

    /**
     * Get the ownership records attached to the batch.
     */
    public function ownerships(): HasMany
    {
        return $this->hasMany(BatchOwnership::class);
    }
}
