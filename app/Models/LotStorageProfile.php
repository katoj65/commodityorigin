<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotStorageProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lot_id',
        'warehouse',
        'storage_location',
        'packaging_type',
        'quantity_bags',
        'bag_weight_kg',
        'net_weight_kg',
        'storage_condition',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_bags' => 'integer',
            'bag_weight_kg' => 'decimal:2',
            'net_weight_kg' => 'decimal:2',
        ];
    }

    /**
     * Get the lot that owns the storage profile.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
