<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BusinessProfile extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'logo_url',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'business_name',
        'registration_number',
        'tax_id',
        'business_type',
        'industry',
        'website',
        'contact_email',
        'contact_phone',
        'employee_count',
        'year_established',
        'description',
        'logo',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employee_count' => 'integer',
            'year_established' => 'integer',
        ];
    }

    /**
     * Get the user that owns the business profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full public URL for the uploaded business logo, or null if none has
     * been set.
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->logo ? Storage::disk('public')->url($this->logo) : null);
    }
}
