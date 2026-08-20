<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class StoreItem extends Model
{
    use HasFactory;

    /**
     * The lifecycle an item moves through while it sits in a store —
     * every transition between these is written to
     * store_item_status_logs so an item's history stays fully traceable.
     *
     * @var array<int, string>
     */
    public const STATUSES = ['available', 'reserved', 'sold', 'shipped', 'delivered', 'archived'];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'name',
        'sku',
        'category',
        'description',
        'price',
        'currency_code',
        'quantity',
        'unit',
        'image',
        'status',
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
            'price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    /**
     * The store this item belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * The full history of status changes for this item, newest first.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(StoreItemStatusLog::class)->latest();
    }

    /**
     * Full public URL for the uploaded item image, or null if none has
     * been set.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image ? Storage::disk('public')->url($this->image) : null);
    }
}
