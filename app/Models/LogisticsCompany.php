<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsCompany extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'coverage_area',
        'contact_email',
        'contact_phone',
        'rating',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'decimal:1',
    ];
}
