<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cooperative extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'registration_number',
        'contact_person',
        'telephone',
        'email',
        'district',
        'sub_county',
        'address',
        'status',
        'notes',
    ];

    public function farmers(): HasMany
    {
        return $this->hasMany(Farmer::class);
    }
}
