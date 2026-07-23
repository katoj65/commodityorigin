<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
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
            'type' => $this->type,
            'is_credit' => $this->isCredit(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'balance_after' => $this->balance_after,
            'description' => $this->description,
            'counterparty_name' => $this->whenLoaded(
                'counterpartyWallet',
                fn () => $this->counterpartyWallet?->user?->name
            ),
            'reference' => $this->reference,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
