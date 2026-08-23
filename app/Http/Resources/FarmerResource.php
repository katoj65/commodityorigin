<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmerResource extends JsonResource
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
            'cooperative_id' => $this->cooperative_id,
            'farmer_number' => $this->farmer_number,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' ')),
            'gender' => $this->gender,
            'date_of_birth' => optional($this->date_of_birth)?->toDateString(),
            'tel' => $this->tel,
            'email' => $this->email,
            'country' => $this->country,
            'region' => $this->region,
            'district' => $this->district,
            'county' => $this->county,
            'subcounty' => $this->subcounty,
            'parish' => $this->parish,
            'village' => $this->village,
            'national_id' => $this->national_id,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'farms_count' => $this->when(isset($this->farms_count), (int) $this->farms_count),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'cooperative' => $this->whenLoaded('cooperative', fn () => $this->cooperative ? [
                'id' => $this->cooperative->id,
                'name' => $this->cooperative->name,
            ] : null),
            'farms' => $this->whenLoaded('farms', fn () => FarmResource::collection($this->farms)->resolve()),
        ];
    }
}
