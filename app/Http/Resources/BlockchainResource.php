<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockchainResource extends JsonResource
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
            'lot_id' => $this->lot_id,
            'network' => $this->network,
            'block_number' => $this->block_number,
            'hash' => $this->hash,
            'previous_hash' => $this->previous_hash,
            'status' => $this->status,
            'confirmations' => $this->confirmations,
            'committed_at' => optional($this->committed_at)?->toIso8601String(),
            'committed_by' => $this->whenLoaded('user', fn (): ?array => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
