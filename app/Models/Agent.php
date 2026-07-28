<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Agent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'icon',
        'description',
        'agent_type',
        'action',
        'status',
        'subject_type',
        'subject_id',
        'input',
        'output',
        'error_message',
        'executed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'input' => 'array',
        'output' => 'array',
        'executed_at' => 'datetime',
    ];

    /**
     * Get the model the agent acted on (e.g. a Lot, LotRequest, or Bid).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The callable functions this agent exposes.
     */
    public function functions(): HasMany
    {
        return $this->hasMany(AgentFunction::class);
    }
}
