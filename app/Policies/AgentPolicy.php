<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;

class AgentPolicy
{
    /**
     * Determine whether the user can create an agent.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the agent details/management page.
     */
    public function view(User $user, Agent $agent): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the agent, including adding
     * functions to it.
     */
    public function update(User $user, Agent $agent): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the agent.
     */
    public function delete(User $user, Agent $agent): bool
    {
        return $user->isAdmin();
    }
}
