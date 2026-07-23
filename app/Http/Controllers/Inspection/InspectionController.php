<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderInspectionResource;
use App\Models\Order;
use App\Models\OrderInspection;
use App\Services\InspectionService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InspectionController extends Controller
{
    public function __construct(
        private readonly InspectionService $inspections,
        private readonly NotificationService $notifications,
    ) {
    }

    /**
     * Display every inspection. Admins see every inspection on the
     * platform, so they can confirm completion; buyers and sellers only
     * see inspections tied to orders they're part of.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Inspection/InspectionPage', [
            'inspections' => OrderInspectionResource::collection($this->inspections->forUser($user->id, $user->isAdmin()))->resolve(),
            'authUserId' => $user->id,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    /**
     * Request an inspection on a confirmed order. Only the confirmed
     * seller may do this, moving the order into the "inspection" status
     * to await the buyer's acknowledgment and the admin's sign-off.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        if ($order->seller_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('Only the confirmed seller can request an inspection.');
        }

        if ($order->status !== 'confirmed') {
            throw ValidationException::withMessages(['status' => 'Order must be confirmed before an inspection can be requested.']);
        }

        $this->inspections->create($order, $request->user()->id);

        $this->notifications->notify(
            userId: $order->buyer_id,
            type: 'order.inspection.requested',
            category: 'orders',
            title: 'Inspection requested',
            body: "The seller requested an inspection for {$order->order_number} ({$order->crop_type}).",
            priority: 'high',
            actionUrl: route('orders.show', $order),
            data: ['order_id' => $order->id, 'order_number' => $order->order_number],
            source: $order,
        );

        return back()->with('success', 'Inspection requested.');
    }

    /**
     * Acknowledge that the inspection has begun. Only the order's buyer
     * may do this, once, and only while the inspection is still pending.
     * From this point neither the buyer nor the seller can act further —
     * the order just waits on the admin to confirm completion.
     */
    public function acknowledge(Request $request, Order $order, OrderInspection $inspection): RedirectResponse
    {
        if ($order->buyer_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('Only the order\'s buyer can acknowledge this inspection.');
        }

        if ($inspection->order_id !== $order->id || $inspection->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This inspection can no longer be acknowledged.']);
        }

        if ($inspection->buyer_acknowledged_at) {
            throw ValidationException::withMessages(['status' => 'You already acknowledged this inspection.']);
        }

        $this->inspections->acknowledgeAsBuyer($inspection);

        $this->notifications->notify(
            userId: $order->seller_id,
            type: 'order.inspection.acknowledged',
            category: 'orders',
            title: 'Inspection acknowledged',
            body: "The buyer acknowledged the inspection for {$order->order_number} ({$order->crop_type}). Waiting on admin to confirm it's complete.",
            priority: 'normal',
            actionUrl: route('orders.show', $order),
            data: ['order_id' => $order->id, 'order_number' => $order->order_number],
            source: $order,
        );

        return back()->with('success', 'Inspection acknowledged.');
    }

    /**
     * Confirm an inspection as complete. Only an admin may do this, and
     * only once the buyer has acknowledged it — advancing the order into
     * processing so shipping can start.
     */
    public function complete(Request $request, Order $order, OrderInspection $inspection): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            throw new AccessDeniedHttpException('Only an admin can confirm an inspection as complete.');
        }

        if ($inspection->order_id !== $order->id || $inspection->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This inspection can no longer be completed.']);
        }

        if (! $inspection->buyer_acknowledged_at) {
            throw ValidationException::withMessages(['status' => 'The buyer must acknowledge the inspection first.']);
        }

        $this->inspections->complete($inspection, $request->user()->id);

        foreach ([$order->buyer_id, $order->seller_id] as $recipientId) {
            $this->notifications->notify(
                userId: $recipientId,
                type: 'order.inspection.completed',
                category: 'orders',
                title: 'Inspection complete',
                body: "The inspection for {$order->order_number} ({$order->crop_type}) is complete. Shipping can begin.",
                priority: 'high',
                actionUrl: route('orders.show', $order),
                data: ['order_id' => $order->id, 'order_number' => $order->order_number],
                source: $order,
            );
        }

        return back()->with('success', 'Inspection marked complete.');
    }
}
