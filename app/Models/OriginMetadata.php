<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OriginMetadata extends Model
{
    protected $table = 'origins_metadata';

    protected $fillable = [
        'name',
        'slug',
        'region',
        'description',
        'flavor_profile',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
