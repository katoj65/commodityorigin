<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'role' => $this->role,
            'status' => $this->status,
            'message' => $this->message,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
