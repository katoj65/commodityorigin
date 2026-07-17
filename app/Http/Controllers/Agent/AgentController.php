<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgentResource;
use App\Models\Agent;
use App\Services\AgentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentController extends Controller
{
    public function __construct(private readonly AgentService $agents)
    {
    }

    /**
     * Display the agents (apps) directory.
     */
    public function index(Request $request): Response
    {
        $agents = $this->agents->query()->latest()->get();

        return Inertia::render('Apps/Agents', [
            'agents' => AgentResource::collection($agents)->resolve(),
            'subscribedAgentIds' => $this->agents->subscribedAgentIds($request->user()->id),
        ]);
    }

    /**
     * Store a newly created agent.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified agent.
     */
    public function show(int $id)
    {
    }

    /**
     * Update the specified agent.
     */
    public function update(Request $request, Agent $agent)
    {
    }

    /**
     * Remove the specified agent.
     */
    public function destroy(Agent $agent)
    {
    }

    /**
     * Subscribe the authenticated user to the specified agent.
     */
    public function userSubscription(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_type' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $this->agents->subscribeUser($request->user()->id, $agent->id, $validated);

        return back()->with('success', 'Subscribed to agent successfully.');
    }

    /**
     * Unsubscribe the authenticated user from the specified agent.
     */
    public function unsubscribe(Request $request, Agent $agent): RedirectResponse
    {
        $this->agents->unsubscribeUser($request->user()->id, $agent->id);

        return back()->with('success', 'Unsubscribed from agent successfully.');
    }
}
