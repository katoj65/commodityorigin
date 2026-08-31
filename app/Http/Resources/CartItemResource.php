<?php

namespace App\Http\Resources;

use App\Models\AgriculturalInput;
use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Line items can point at different purchasable models (a coffee
     * Market listing or an AgriculturalInput store product), each with its
     * own idea of "unit", "image", and "seller" — this resource normalizes
     * those into one shape so the cart/checkout UI doesn't need to branch
     * on type for every field.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cartable = $this->whenLoaded('cartable');
        $unitPrice = (float) $this->unit_price;

        return match (true) {
            $cartable instanceof Market => $this->marketFields($cartable, $unitPrice),
            $cartable instanceof AgriculturalInput => $this->agriculturalInputFields($cartable, $unitPrice),
            default => $this->missingCartableFields($unitPrice),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function marketFields(Market $market, float $unitPrice): array
    {
        return [
            'id' => $this->id,
            'cartable_type' => 'market',
            'cartable_id' => $market->id,
            'status' => $this->status,
            'quantity' => (int) $this->quantity,
            'unit' => $market->unit ?? 'kg',
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $this->quantity, 2),
            'current_price' => (float) $market->price_per_unit,
            'available_quantity' => (float) ($market->available_quantity ?? $market->quantity),
            'name' => $market->title ?? $market->lot_code,
            'subtitle' => trim(($market->origin ?? 'Origin unknown').' · '.($market->process ?? '—'), ' ·'),
            'image_url' => $market->image ? Storage::disk('public')->url($market->image) : null,
            'quality_score' => $market->quality_score !== null ? (float) $market->quality_score : null,
            'seller_name' => $market->user?->name,
            'detail_route' => ['name' => 'market.show', 'params' => $market->id],
            'lot_code' => $market->lot_code,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function agriculturalInputFields(AgriculturalInput $input, float $unitPrice): array
    {
        return [
            'id' => $this->id,
            'cartable_type' => 'agricultural_input',
            'cartable_id' => $input->id,
            'status' => $this->status,
            'quantity' => (int) $this->quantity,
            'unit' => $input->unit,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $this->quantity, 2),
            'current_price' => (float) $input->price,
            'available_quantity' => (float) $input->stock_quantity,
            'name' => $input->name,
            'subtitle' => trim(($input->tag ?? ucfirst($input->category)).' · '.($input->manufacturer ?? '—'), ' ·'),
            'image_url' => $input->image ? Storage::disk('public')->url($input->image) : null,
            'quality_score' => null,
            'seller_name' => $input->creator?->name,
            'detail_route' => null,
            'lot_code' => $input->sku,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }

    /**
     * The purchasable item behind this line was deleted after it was added
     * to the cart — render enough to let the user remove it, nothing more.
     *
     * @return array<string, mixed>
     */
    private function missingCartableFields(float $unitPrice): array
    {
        return [
            'id' => $this->id,
            'cartable_type' => null,
            'cartable_id' => null,
            'status' => $this->status,
            'quantity' => (int) $this->quantity,
            'unit' => 'unit',
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $this->quantity, 2),
            'current_price' => null,
            'available_quantity' => 0,
            'name' => 'No longer available',
            'subtitle' => 'This item was removed from the store.',
            'image_url' => null,
            'quality_score' => null,
            'seller_name' => null,
            'detail_route' => null,
            'lot_code' => null,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
