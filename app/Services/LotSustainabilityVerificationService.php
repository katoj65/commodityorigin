<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\LotSustainabilityVerification;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records and retrieves a lot's sustainability/compliance checklist items
 * (e.g. "EUDR Deforestation-Free Pass" — VERIFIED) shown on the lot's
 * Sustainability & Compliance card.
 */
class LotSustainabilityVerificationService
{
    /**
     * Add a checklist item to a lot.
     *
     * @param  array{item: string, description?: ?string, icon?: ?string, order?: int, status?: string}  $data
     */
    public function store(Lot $lot, array $data): LotSustainabilityVerification
    {
        return LotSustainabilityVerification::query()->create([
            'lot_id' => $lot->id,
            'item' => $data['item'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'order' => $data['order'] ?? 0,
            'status' => $data['status'] ?? 'pending',
        ]);
    }

    /**
     * Get a lot's checklist items, in display order.
     *
     * @return Collection<int, LotSustainabilityVerification>
     */
    public function forLot(Lot $lot): Collection
    {
        return LotSustainabilityVerification::query()
            ->where('lot_id', $lot->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Update a checklist item — e.g. moving its status from pending to
     * verified once evidence is reviewed.
     *
     * @param  array{item?: string, description?: ?string, icon?: ?string, order?: int, status?: string}  $data
     */
    public function update(LotSustainabilityVerification $item, array $data): LotSustainabilityVerification
    {
        $item->update($data);

        return $item;
    }

    /**
     * Remove a mistaken or duplicate checklist item.
     */
    public function delete(LotSustainabilityVerification $item): void
    {
        $item->delete();
    }
}
