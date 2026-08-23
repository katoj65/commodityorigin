<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cooperative_id',
        'farmer_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'tel',
        'email',
        'country',
        'region',
        'district',
        'county',
        'subcounty',
        'parish',
        'village',
        'national_id',
        'status',
        'verification_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cooperative(): BelongsTo
    {
        return $this->belongsTo(Cooperative::class);
    }

    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class);
    }
}
