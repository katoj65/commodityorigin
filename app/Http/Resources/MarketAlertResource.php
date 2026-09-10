<?php

namespace App\Http\Resources;

use App\Models\MarketAlert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketAlertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'label' => MarketAlert::TYPES[$this->type] ?? $this->type,
            'enabled' => (bool) $this->enabled,
        ];
    }
}
