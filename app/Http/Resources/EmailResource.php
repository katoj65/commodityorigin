<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailResource extends JsonResource
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
            'recipient' => $this->recipient,
            'subject' => $this->subject,
            'body' => $this->body,
            'action_text' => $this->action_text,
            'action_url' => $this->action_url,
            'status' => $this->status,
            'error' => $this->error,
            'sent_at' => optional($this->sent_at)?->toDateTimeString(),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
