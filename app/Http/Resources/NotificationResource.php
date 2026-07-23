<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'type' => $this->type,
            'category' => $this->category,
            'priority' => $this->priority,
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->action_url,
            'icon' => $this->icon,
            'data' => $this->data,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'is_read' => ! is_null($this->read_at),
            'read_at' => optional($this->read_at)?->toDateTimeString(),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
