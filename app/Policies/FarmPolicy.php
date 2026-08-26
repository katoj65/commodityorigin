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
     * Determine whether the user can view a single farm profile.
     * Creator and admins only.
     */
    public function view(User $user, Farm $farm): bool
    {
        return $user->isAdmin() || $farm->user_id === $user->id;
    }

    /**
     * Determine whether the user can create a farm. Admins and anyone
     * subscribed to the Farmer Agent may self-register a farm (the create
     * form itself decides whether they're the farmer or are registering
     * one).
     */
    public function create(User $user): bool
    {
        return $this->allowed($user);
    }

    /**
     * Determine whether the user can update the farm. Admin or creator only.
     */
    public function update(User $user, Farm $farm): bool
    {
        return $user->isAdmin() || $farm->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the farm. Admin or creator only.
     */
    public function delete(User $user, Farm $farm): bool
    {
        return $user->isAdmin() || $farm->user_id === $user->id;
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
