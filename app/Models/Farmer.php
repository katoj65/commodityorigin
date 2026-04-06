<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Farmer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'telephone',
        'email',
        'district',
        'sub_county',
        'coffee_type',
        'cooperative',
        'farm_size',
        'notes',
    ];

    /**
     * Get the user who owns the farmer record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
