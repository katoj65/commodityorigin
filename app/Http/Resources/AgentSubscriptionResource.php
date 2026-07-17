<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentSubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'agent_id' => $this->agent_id,
            'subscription_type' => $this->subscription_type,
            'token' => $this->token,
            'expiry_date' => optional($this->expiry_date)?->toDateTimeString(),
            'agent' => $this->whenLoaded('agent', function (): ?array {
                if (!$this->agent) {
                    return null;
                }

                return [
                    'id' => $this->agent->id,
                    'name' => $this->agent->name,
                    'description' => $this->agent->description,
                    'agent_type' => $this->agent->agent_type,
                ];
            }),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
