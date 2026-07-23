<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
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
            'balance' => $this->balance,
            'locked_balance' => $this->locked_balance,
            'available_balance' => $this->availableBalance(),
            'currency' => $this->currency,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
