<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderIntentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->whenLoaded('user', fn () => $this->user->profile);

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'user_name' => $this->whenLoaded('user', fn () => $this->user->name),
            'profile' => $this->whenLoaded('user', fn () => [
                'role' => $this->user->role,
                'bio' => $profile?->bio,
                'location' => collect([$profile?->city, $profile?->state, $profile?->country])->filter()->implode(', '),
                'email_verified' => (bool) $this->user->email_verified_at,
                'member_since' => optional($this->user->created_at)?->toDateString(),
            ]),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
