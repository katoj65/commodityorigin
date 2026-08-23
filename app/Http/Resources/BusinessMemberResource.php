<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessMemberResource extends JsonResource
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
            'business_profile_id' => $this->business_profile_id,
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'gender' => $this->gender,
            'date_of_birth' => optional($this->date_of_birth)?->toDateString(),
            'id_number' => $this->id_number,
            'designation' => $this->designation,
            'position' => $this->position,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'address' => $this->address,
            'status' => $this->status,
            'photo_url' => $this->photo_url,
            'bio' => $this->bio,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
