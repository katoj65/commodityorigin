<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ForecastResource extends JsonResource
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
            'crop_type' => $this->crop_type,
            'category' => $this->category,
            'horizon' => $this->horizon,
            'headline' => $this->headline,
            'detail' => $this->detail,
            'direction' => $this->direction,
            'confidence' => $this->confidence !== null ? (int) $this->confidence : null,
            'effective_date' => optional($this->effective_date)?->toDateString(),
        ];
    }
}
