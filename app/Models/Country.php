<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'iso2',
        'iso3',
        'phone_code',
        'region',
        'subregion',
        'currency_code',
        'currency_name',
        'is_coffee_producer',
        'coffee_production_bags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_coffee_producer' => 'boolean',
        'coffee_production_bags' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['flag_emoji'];

    /**
     * The flag emoji, derived from the ISO2 code's regional indicator symbols.
     */
    protected function flagEmoji(): string
    {
        return collect(str_split(strtoupper($this->iso2)))
            ->map(fn (string $letter): string => mb_chr(127397 + ord($letter)))
            ->implode('');
    }

    public function getFlagEmojiAttribute(): string
    {
        return $this->flagEmoji();
    }
}
