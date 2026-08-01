<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;
use App\Services\AgentService;

class FarmPolicy
{
    public function __construct(private readonly AgentService $agents)
    {
    }

    /**
     * Determine whether the user can view the farm directory.
     */
    public function viewAny(User $user): bool
    {
        return $this->allowed($user);
    }

    /**
     * Determine whether the user can view a single farm.
     */
    public function view(User $user, Farm $farm): bool
    {
        return $this->allowed($user);
    }

    /**
     * Determine whether the user can create a farm.
     */
    public function create(User $user): bool
    {
        return $this->allowed($user);
    }

    /**
     * Admins have full access; everyone else must be subscribed to the
     * Farmer Agent.
     */
    private function allowed(User $user): bool
    {
        return $user->isAdmin() || $this->agents->isSubscribedToAgentType($user->id, 'farmer_agent');
    }
}
