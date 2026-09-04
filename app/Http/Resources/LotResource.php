<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotResource extends JsonResource
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
            'lot_number' => $this->lot_number,
            'lot_name' => $this->lot_name,
            'description' => $this->description,
            'image' => $this->image,
            'process' => $this->process,
            'grade' => $this->grade,
            'variety' => $this->variety,
            'origin' => $this->origin,
            'region' => $this->region,
            'altitude' => $this->altitude,
            'year_of_harvest' => $this->year_of_harvest,
            'moisture' => $this->moisture,
            'defects_percentage' => $this->defects_percentage,
            'screen' => $this->screen,
            'net_weight_kg' => $this->net_weight_kg,
            'quantity_bags' => $this->quantity_bags,
            'bag_weight_kg' => $this->bag_weight_kg,
            'price' => $this->price,
            'currency' => $this->currency,
            'quality_score' => $this->quality_score,
            'acidity' => $this->acidity,
            'body' => $this->body,
            'flavor' => $this->flavor,
            'aroma' => $this->aroma,
            'balance' => $this->balance,
            'aftertaste' => $this->aftertaste,
            'packaging_type' => $this->packaging_type,
            'status' => $this->status,
            'notes' => $this->notes,
            'qr_code' => $this->qr_code,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'can_manage' => $request->user() ? $request->user()->can('update', $this->resource) : false,
            'is_published' => $this->when($this->relationLoaded('market'), fn (): bool => $this->market !== null, false),
            'market' => $this->whenLoaded('market', fn (): ?array => $this->market ? [
                'id' => $this->market->id,
                'title' => $this->market->title,
                'status' => $this->market->status,
                'available_from' => $this->market->available_from,
                'delivery_method' => $this->market->delivery_method,
                'incoterm' => $this->market->incoterm,
                'dispatch' => $this->market->dispatch,
                'transport_arrangement' => $this->market->transport_arrangement,
                'insurance_arrangement' => $this->market->insurance_arrangement,
            ] : null),
            'lot_batches' => $this->whenLoaded('lotBatches', fn (): array => LotBatchResource::collection($this->lotBatches)->resolve()),
            'images' => $this->whenLoaded('images', fn (): array => LotImageResource::collection($this->images)->resolve()),
            'flavors' => $this->whenLoaded('flavors', fn (): array => $this->flavors->map(fn ($flavor): array => [
                'id' => $flavor->id,
                'slug' => $flavor->slug,
                'name' => $flavor->name,
            ])->all()),
            'blockchain' => $this->whenLoaded('blockchain', fn (): ?array => $this->blockchain ? BlockchainResource::make($this->blockchain)->resolve() : null),
            'user' => $this->whenLoaded('user', fn (): ?array => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
        ];
    }
}
