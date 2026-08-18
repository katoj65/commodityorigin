<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AgriculturalInputResource extends JsonResource
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
            'creator_name' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'name' => $this->name,
            'category' => $this->category,
            'tag' => $this->tag,
            'description' => $this->description,
            'price' => (float) $this->price,
            'image' => $this->image,
            'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'sku' => $this->sku,
            'stock_quantity' => $this->stock_quantity,
            'unit' => $this->unit,
            'manufacturer' => $this->manufacturer,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
