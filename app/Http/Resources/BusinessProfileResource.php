<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessProfileResource extends JsonResource
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
            'business_name' => $this->business_name,
            'registration_number' => $this->registration_number,
            'tax_id' => $this->tax_id,
            'business_type' => $this->business_type,
            'industry' => $this->industry,
            'website' => $this->website,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'employee_count' => $this->employee_count,
            'year_established' => $this->year_established,
            'description' => $this->description,
            'logo_url' => $this->logo_url,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'owner_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'members_count' => $this->when(isset($this->members_count), (int) $this->members_count),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
