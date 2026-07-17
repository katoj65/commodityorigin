<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentSubscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AgentService
{
    /**
     * Get a base query builder for agents.
     */
    public function query(): Builder
    {
        return Agent::query();
    }

    /**
     * Create a new agent.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Agent
    {
        return Agent::query()->create($data)->refresh();
    }

    /**
     * Update an existing agent.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Agent $agent, array $data): Agent
    {
        $agent->update($data);

        return $agent;
    }

    /**
     * Find a single agent by id.
     */
    public function show(int $id): Agent
    {
        return Agent::query()->findOrFail($id);
    }

    /**
     * Delete an agent.
     */
    public function destroy(Agent $agent): void
    {
        $agent->delete();
    }

    /**
     * Delete a single agent by id.
     */
    public function destroyById(int $id): void
    {
        Agent::query()->findOrFail($id)->delete();
    }

    /**
     * Get a base query builder for agent subscriptions.
     */
    public function subscriptionQuery(): Builder
    {
        return AgentSubscription::query();
    }

    /**
     * Create a new agent subscription.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSubscription(array $data): AgentSubscription
    {
        return AgentSubscription::query()->create($data);
    }

    /**
     * Determine whether a user is already subscribed to an agent.
     */
    public function isSubscribed(int $userId, int $agentId): bool
    {
        return AgentSubscription::query()
            ->where('user_id', $userId)
            ->where('agent_id', $agentId)
            ->exists();
    }

    /**
     * Get the ids of every agent a user is subscribed to.
     *
     * @return array<int, int>
     */
    public function subscribedAgentIds(int $userId): array
    {
        return AgentSubscription::query()
            ->where('user_id', $userId)
            ->pluck('agent_id')
            ->all();
    }

    /**
     * Subscribe a user to an agent, generating the access token and
     * defaulting the subscription type and expiry when not provided.
     *
     * @param  array<string, mixed>  $data
     */
    public function subscribeUser(int $userId, int $agentId, array $data = []): AgentSubscription
    {
        if ($this->isSubscribed($userId, $agentId)) {
            throw ValidationException::withMessages([
                'agent' => 'You are already subscribed to this agent.',
            ]);
        }

        return $this->createSubscription([
            'user_id' => $userId,
            'agent_id' => $agentId,
            'subscription_type' => $data['subscription_type'] ?? 'free',
            'token' => Str::random(40),
            'expiry_date' => $data['expiry_date'] ?? now()->addYear(),
        ]);
    }

    /**
     * Unsubscribe a user from an agent.
     */
    public function unsubscribeUser(int $userId, int $agentId): void
    {
        AgentSubscription::query()
            ->where('user_id', $userId)
            ->where('agent_id', $agentId)
            ->delete();
    }

    /**
     * Find a single agent subscription by id.
     */
    public function showSubscription(int $id): AgentSubscription
    {
        return AgentSubscription::query()->findOrFail($id);
    }

    /**
     * Update an existing agent subscription.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSubscription(AgentSubscription $subscription, array $data): AgentSubscription
    {
        $subscription->update($data);

        return $subscription;
    }

    /**
     * Delete an agent subscription.
     */
    public function destroySubscription(AgentSubscription $subscription): void
    {
        $subscription->delete();
    }
}
