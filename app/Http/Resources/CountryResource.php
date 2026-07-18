<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            'name' => $this->name,
            'iso2' => $this->iso2,
            'iso3' => $this->iso3,
            'phone_code' => $this->phone_code,
            'region' => $this->region,
            'subregion' => $this->subregion,
            'currency_code' => $this->currency_code,
            'currency_name' => $this->currency_name,
            'flag_emoji' => $this->flag_emoji,
            'is_coffee_producer' => (bool) $this->is_coffee_producer,
            'coffee_production_bags' => $this->coffee_production_bags !== null ? (int) $this->coffee_production_bags : null,
        ];
    }
}
