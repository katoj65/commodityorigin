<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgentResource;
use App\Models\Agent;
use App\Services\AgentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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
        $agents = $this->agents->query()->with('functions')->latest()->get();

        return Inertia::render('Apps/Agents', [
            'agents' => AgentResource::collection($agents)->resolve(),
            'subscribedAgentIds' => $this->agents->subscribedAgentIds($request->user()->id),
            'canCreateAgent' => $request->user()->can('create', Agent::class),
        ]);
    }

    /**
     * Store a newly created agent.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Agent::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'agent_type' => ['required', 'string', 'max:255', 'unique:agents,agent_type'],
            'action' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:pending,active,success,failed'],
        ]);

        $this->agents->create($validated);

        return back()->with('success', 'Agent created successfully.');
    }

    /**
     * Display the specified agent's details/management page. Admin only.
     */
    public function show(Agent $agent): Response
    {
        Gate::authorize('view', $agent);

        $agent->load('functions');

        return Inertia::render('Agent/AgentDetails', [
            'agent' => (new AgentResource($agent))->resolve(),
        ]);
    }

    /**
     * Update the specified agent.
     */
    public function update(Request $request, Agent $agent): RedirectResponse
    {
        Gate::authorize('update', $agent);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'agent_type' => ['required', 'string', 'max:255', Rule::unique('agents', 'agent_type')->ignore($agent->id)],
            'action' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:pending,active,success,failed'],
        ]);

        $this->agents->update($agent, $validated);

        return back()->with('success', 'Agent updated successfully.');
    }

    /**
     * Remove the specified agent.
     */
    public function destroy(Agent $agent): RedirectResponse
    {
        Gate::authorize('delete', $agent);

        $this->agents->destroy($agent);

        return redirect()->route('apps.index')->with('success', 'Agent deleted successfully.');
    }

    /**
     * Add a new function to the specified agent. Admin only.
     */
    public function storeFunction(Request $request, Agent $agent): RedirectResponse
    {
        Gate::authorize('update', $agent);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique('agent_functions', 'slug')->where(fn ($query) => $query->where('agent_id', $agent->id)),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'parameters' => ['nullable', 'array'],
        ]);

        $this->agents->createFunction($agent, $validated);

        return back()->with('success', 'Function added successfully.');
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
