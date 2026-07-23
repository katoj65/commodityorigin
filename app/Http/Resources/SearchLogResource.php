<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchLogResource extends JsonResource
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
            'query' => $this->query,
            'filters' => $this->filters ?? [],
            'results_count' => $this->results_count,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
