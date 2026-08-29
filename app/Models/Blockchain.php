<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blockchain extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lot_id',
        'user_id',
        'network',
        'block_number',
        'hash',
        'previous_hash',
        'status',
        'confirmations',
        'committed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'block_number' => 'integer',
        'confirmations' => 'integer',
        'committed_at' => 'datetime',
    ];

    /**
     * Get the lot this block was committed for.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Get the user who committed this lot to the chain.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
