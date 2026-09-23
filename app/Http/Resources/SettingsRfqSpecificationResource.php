<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsRfqSpecificationResource extends JsonResource
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
            'target_price' => $this->target_price,
            'destination' => $this->destination,
            'payment_terms' => $this->payment_terms,
            'grade' => $this->grade,
            'type' => $this->type,
            'min_weight' => $this->min_weight,
            'max_weight' => $this->max_weight,
            'incoterms' => $this->incoterms,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
