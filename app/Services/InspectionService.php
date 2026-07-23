<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderInspection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class InspectionService
{
    /**
     * Get a base query builder for inspections.
     */
    public function query(): Builder
    {
        return OrderInspection::query();
    }

    /**
     * Get every inspection an admin can act on, or that a buyer/seller is
     * party to via its order, newest first. Admins see the whole platform
     * so they can confirm completion; everyone else only sees inspections
     * tied to orders they're part of.
     *
     * @return Collection<int, OrderInspection>
     */
    public function forUser(int $userId, bool $isAdmin = false): Collection
    {
        return OrderInspection::query()
            ->with(['requestedBy', 'completedBy', 'order.buyer', 'order.seller'])
            ->when(! $isAdmin, fn (Builder $query) => $query->whereHas(
                'order',
                fn (Builder $order) => $order->where('buyer_id', $userId)->orWhere('seller_id', $userId)
            ))
            ->latest()
            ->get();
    }

    /**
     * Request an inspection on a confirmed order — creates the inspection
     * record and moves the order into the "inspection" status, awaiting
     * the buyer's acknowledgment and the admin's completion sign-off.
     */
    public function create(Order $order, int $sellerId): OrderInspection
    {
        $inspection = $order->inspections()->create([
            'requested_by' => $sellerId,
            'status' => 'pending',
        ]);

        $order->update(['status' => 'inspection']);

        return $inspection;
    }

    /**
     * Update an existing inspection.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(OrderInspection $inspection, array $data): OrderInspection
    {
        $inspection->update($data);

        return $inspection;
    }

    /**
     * Delete an inspection.
     */
    public function destroy(OrderInspection $inspection): void
    {
        $inspection->delete();
    }

    /**
     * Record the buyer's acknowledgment that the inspection has begun.
     * From this point, neither the buyer nor the seller can act further —
     * only an admin can advance the order, by confirming the inspection
     * complete.
     */
    public function acknowledgeAsBuyer(OrderInspection $inspection): OrderInspection
    {
        return $this->update($inspection, ['buyer_acknowledged_at' => now()]);
    }

    /**
     * Confirm an inspection as complete — an admin's sign-off, once the
     * buyer has acknowledged it — and advance the order into processing
     * so shipping can start.
     */
    public function complete(OrderInspection $inspection, int $adminId): OrderInspection
    {
        $this->update($inspection, [
            'status' => 'completed',
            'completed_by' => $adminId,
            'completed_at' => now(),
        ]);

        $inspection->order->update(['status' => 'processing']);

        return $inspection;
    }
}
