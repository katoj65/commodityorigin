<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Warehouse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_id',
        'item_type',
        'storage_bay',
        'date_stored',
        'quantity_stored_kg',
        'climate_ambient',
        'physical_pallet',
        'packaging_spec',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_stored' => 'date',
            'quantity_stored_kg' => 'decimal:2',
        ];
    }

    /**
     * Get the batch, farm collection, or lot this warehousing record
     * belongs to.
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
