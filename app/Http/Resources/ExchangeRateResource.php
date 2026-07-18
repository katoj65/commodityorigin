<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
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
            'pair' => "{$this->base_currency} / {$this->quote_currency}",
            'base_currency' => $this->base_currency,
            'quote_currency' => $this->quote_currency,
            'rate' => (float) $this->rate,
            'daily_change_percent' => $this->daily_change_percent !== null ? (float) $this->daily_change_percent : null,
            'up' => $this->daily_change_percent !== null ? (float) $this->daily_change_percent >= 0 : null,
            'recorded_at' => optional($this->recorded_at)?->toDateTimeString(),
        ];
    }
}
