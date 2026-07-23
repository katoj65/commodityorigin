<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderInspectionResource;
use App\Http\Resources\OrderIntentResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderIntent;
use App\Services\EscrowService;
use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OrderController extends Controller
{
    private const STATUSES = ['open', 'pending', 'confirmed', 'inspection', 'processing', 'shipped', 'delivered', 'cancelled', 'withdrawn'];

    public function __construct(
        private readonly OrderService $orders,
        private readonly NotificationService $notifications,
        private readonly EscrowService $escrow,
    ) {
    }

    /**
     * Display the orders page — every order the authenticated user placed
     * or is fulfilling, plus every other request and offer on the
     * marketplace, at any status, visible to the whole community.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        return Inertia::render('Order/OrderPage', [
            'orders' => OrderResource::collection($this->orders->ordersForUser($userId))->resolve(),
            'openOrders' => OrderResource::collection($this->orders->openOrdersExcluding($userId))->resolve(),
            'authUserId' => $userId,
        ]);
    }

    /**
     * Post a new open order — a "request" (buyer looking to buy, no seller
     * yet) or an "offer" (seller looking to sell, no buyer yet).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['request', 'offer'])],
            'crop_type' => ['required', 'string', 'max:255'],
            'variety' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->orders->create($validated, $request->user()->id);

        $message = $validated['type'] === 'offer'
            ? 'Offer posted — waiting for buyers to express interest.'
            : 'Order posted — waiting for sellers to express interest.';

        return back()->with('success', $message);
    }

    /**
     * Display a single order's dedicated page. Every order is publicly
     * viewable by any authenticated user, at any status — only mutating it
     * is restricted to its buyer/seller. Contact details are only shared
     * with the two parties, never with public viewers.
     */
    public function show(Request $request, Order $order): Response
    {
        $userId = $request->user()->id;
        $isParty = in_array($userId, [$order->buyer_id, $order->seller_id], true);
        $creatorId = $order->type === 'offer' ? $order->seller_id : $order->buyer_id;

        $order->load(['buyer', 'seller.profile', 'inspection.requestedBy', 'inspection.completedBy']);
        $payload = OrderResource::make($order)->resolve();

        if (! $isParty) {
            $payload = array_merge($payload, [
                'buyer_email' => null,
                'buyer_phone' => null,
                'seller_email' => null,
                'seller_phone' => null,
            ]);
        }

        if ($order->seller) {
            $profile = $order->seller->profile;

            $payload['seller_profile'] = [
                'role' => $order->seller->role,
                'bio' => $profile?->bio,
                'location' => collect([$profile?->city, $profile?->state, $profile?->country])->filter()->implode(', '),
                'email_verified' => (bool) $order->seller->email_verified_at,
                'member_since' => optional($order->seller->created_at)?->toDateString(),
            ];

            $payload['seller_history'] = $this->orders->tradeHistorySummary($order->seller_id);
        }

        $payload['my_intent_id'] = $order->intents()
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->value('id');

        if ($creatorId === $userId) {
            $pendingIntents = $order->intents()->with('user.profile')->where('status', 'pending')->latest()->get();

            $payload['intents'] = OrderIntentResource::collection($pendingIntents)
                ->resolve();

            foreach ($payload['intents'] as $index => $intent) {
                $payload['intents'][$index]['history'] = $this->orders->tradeHistorySummary($intent['user_id']);
            }
        }

        $payload['inspection'] = $order->inspection ? OrderInspectionResource::make($order->inspection)->resolve() : null;

        return Inertia::render('Order/OrderShowPage', [
            'order' => $payload,
            'authUserId' => $userId,
            'isAdmin' => $request->user()->isAdmin(),
        ]);
    }

    /**
     * Express intent to fulfill an open order — a seller on a request, a
     * buyer on an offer. Multiple parties may express intent; the order's
     * creator later confirms exactly one.
     */
    public function storeIntent(Request $request, Order $order): RedirectResponse
    {
        $userId = $request->user()->id;
        $creatorId = $order->type === 'offer' ? $order->seller_id : $order->buyer_id;

        if ($creatorId === $userId) {
            throw ValidationException::withMessages(['status' => 'You cannot express interest in your own order.']);
        }

        if (! in_array($order->status, ['open', 'pending'], true)) {
            throw ValidationException::withMessages(['status' => 'This order is no longer accepting interest.']);
        }

        if ($order->intents()->where('user_id', $userId)->exists()) {
            throw ValidationException::withMessages(['status' => 'You already expressed interest in this order.']);
        }

        $this->orders->createIntent($order, $userId);

        $this->notifications->notify(
            userId: $creatorId,
            type: 'order.intent',
            category: 'orders',
            title: 'New interest in your '.($order->type === 'offer' ? 'offer' : 'request'),
            body: "{$request->user()->name} expressed interest in {$order->order_number} ({$order->crop_type}).",
            priority: 'normal',
            actionUrl: route('orders.show', $order),
            data: ['order_id' => $order->id, 'order_number' => $order->order_number],
            source: $order,
        );

        return back()->with('success', 'Interest sent to the order creator.');
    }

    /**
     * Confirm one intent as the order's counterparty. Only the order's
     * creator may do this; every other pending intent on the order is
     * declined.
     */
    public function confirmIntent(Request $request, Order $order, OrderIntent $intent): RedirectResponse
    {
        $creatorId = $order->type === 'offer' ? $order->seller_id : $order->buyer_id;

        if ($creatorId !== $request->user()->id) {
            throw new AccessDeniedHttpException('Only the order creator can confirm an interested party.');
        }

        if ($intent->order_id !== $order->id || $intent->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This interest can no longer be confirmed.']);
        }

        $declinedUserIds = $order->intents()
            ->where('id', '!=', $intent->id)
            ->where('status', 'pending')
            ->pluck('user_id');

        $this->orders->confirmIntent($order, $intent);

        $this->notifications->notify(
            userId: $intent->user_id,
            type: 'order.intent.confirmed',
            category: 'orders',
            title: 'You were confirmed on an order',
            body: "You were confirmed to fulfill {$order->order_number} ({$order->crop_type}).",
            priority: 'high',
            actionUrl: route('orders.show', $order),
            data: ['order_id' => $order->id, 'order_number' => $order->order_number],
            source: $order,
        );

        foreach ($declinedUserIds as $declinedUserId) {
            $this->notifications->notify(
                userId: $declinedUserId,
                type: 'order.intent.declined',
                category: 'orders',
                title: 'Your interest was not selected',
                body: "The creator of {$order->order_number} ({$order->crop_type}) chose another party.",
                priority: 'normal',
                actionUrl: route('orders.index'),
                data: ['order_id' => $order->id, 'order_number' => $order->order_number],
                source: $order,
            );
        }

        return back()->with('success', 'Confirmed successfully.');
    }

    /**
     * Withdraw an expression of intent. Only the interested party may do
     * this, and only while it's still pending.
     */
    public function destroyIntent(Request $request, Order $order, OrderIntent $intent): RedirectResponse
    {
        if ($intent->order_id !== $order->id || $intent->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this interest.');
        }

        if ($intent->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This interest can no longer be withdrawn.']);
        }

        $this->orders->destroyIntent($intent);

        return back()->with('success', 'Interest withdrawn.');
    }

    /**
     * Withdraw an order. If it's still open, it's deleted outright — only
     * the order's strict creator may do this. Otherwise it's marked as
     * withdrawn so the record and its history are preserved. Only the
     * party who created it (the buyer on a request, the seller on an
     * offer) may withdraw it, and only before it's been confirmed.
     */
    public function withdraw(Request $request, Order $order): RedirectResponse
    {
        $creatorId = $order->type === 'offer' ? $order->seller_id : $order->buyer_id;

        if ($creatorId !== $request->user()->id) {
            throw new AccessDeniedHttpException('Only the order creator can withdraw it.');
        }

        if (! in_array($order->status, ['open', 'pending'], true)) {
            throw ValidationException::withMessages(['status' => 'This order can no longer be withdrawn.']);
        }

        $wasOpen = $order->status === 'open';
        $message = $wasOpen ? 'Order deleted successfully.' : 'Order withdrawn successfully.';

        if ($order->status === 'pending') {
            $interestedUserIds = $order->intents()->where('status', 'pending')->pluck('user_id');

            foreach ($interestedUserIds as $interestedUserId) {
                $this->notifications->notify(
                    userId: $interestedUserId,
                    type: 'order.withdrawn',
                    category: 'orders',
                    title: 'An order you were interested in was withdrawn',
                    body: "{$order->order_number} ({$order->crop_type}) was withdrawn by its creator.",
                    priority: 'high',
                    actionUrl: route('orders.index'),
                    data: ['order_id' => $order->id, 'order_number' => $order->order_number],
                    source: $order,
                );
            }
        }

        $this->orders->withdraw($order);

        // An open order is deleted outright, so its show page no longer
        // exists — send the user back to the order list instead of
        // redirecting back into a 404.
        if ($wasOpen) {
            return redirect()->route('orders.index')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Repost a cancelled order as a brand new open order, copying its
     * crop details. Only the original creator may do this.
     */
    public function repost(Request $request, Order $order): RedirectResponse
    {
        $creatorId = $order->type === 'offer' ? $order->seller_id : $order->buyer_id;

        if ($creatorId !== $request->user()->id) {
            throw new AccessDeniedHttpException('Only the order creator can repost it.');
        }

        if ($order->status !== 'cancelled') {
            throw ValidationException::withMessages(['status' => 'Only a cancelled order can be reposted.']);
        }

        $repost = $this->orders->create([
            'type' => $order->type,
            'crop_type' => $order->crop_type,
            'variety' => $order->variety,
            'grade' => $order->grade,
            'quantity' => $order->quantity,
            'unit_price' => $order->unit_price,
            'notes' => $order->notes,
        ], $request->user()->id);

        $message = 'Reposted as a new '.($order->type === 'offer' ? 'offer' : 'request').'.';

        return redirect()->route('orders.show', $repost)->with('success', $message);
    }

    /**
     * Update an order's status. Buyers may only cancel; sellers may
     * advance a confirmed order through fulfillment, or cancel it. Only an
     * admin may activate shipping (processing → shipped) — the seller just
     * waits once processing starts. Activating shipping also holds and
     * releases the order's funds in escrow, from the buyer's wallet into
     * the seller's. Moving to "confirmed" happens exclusively by
     * confirming a specific intent — see confirmIntent().
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();
        $isBuyer = $order->buyer_id === $user->id;
        $isSeller = $order->seller_id === $user->id;
        $isAdmin = $user->isAdmin();

        if (! $isBuyer && ! $isSeller && ! $isAdmin) {
            throw new AccessDeniedHttpException('You are not part of this order.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $status = $validated['status'];

        if ($status === 'confirmed') {
            throw ValidationException::withMessages(['status' => 'Confirm a specific interested party instead.']);
        }

        if (! $isBuyer && ! $isSeller) {
            if ($status !== 'shipped' || $order->status !== 'processing') {
                throw ValidationException::withMessages(['status' => 'Admins may only activate shipping on a processing order.']);
            }
        } elseif ($isBuyer && $status !== 'cancelled') {
            throw ValidationException::withMessages(['status' => 'Buyers may only cancel an order.']);
        } elseif ($isSeller && $status === 'shipped') {
            throw ValidationException::withMessages(['status' => 'Only an admin can activate shipping.']);
        }

        if ($status === 'cancelled' && ! in_array($order->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages(['status' => 'This order can no longer be cancelled once inspection has begun.']);
        }

        if (! $isBuyer && ! $isSeller && $status === 'shipped') {
            $this->escrow->holdAndRelease($order, $user->id);
        }

        $this->orders->update($order, $validated);

        $recipientIds = ! $isBuyer && ! $isSeller
            ? [$order->buyer_id, $order->seller_id]
            : [$isBuyer ? $order->seller_id : $order->buyer_id];

        foreach ($recipientIds as $recipientId) {
            $this->notifications->notify(
                userId: $recipientId,
                type: "order.{$status}",
                category: 'orders',
                title: $status === 'cancelled' ? 'Order cancelled' : 'Order '.$status,
                body: "{$order->order_number} ({$order->crop_type}) is now {$status}.",
                priority: $status === 'cancelled' ? 'high' : 'normal',
                actionUrl: route('orders.index'),
                data: ['order_id' => $order->id, 'order_number' => $order->order_number],
                source: $order,
            );
        }

        return back()->with('success', 'Order updated successfully.');
    }

    /**
     * Permanently delete an order. Only the party who created it (the buyer
     * on a request, the seller on an offer) may delete it, and only while
     * it's still open — never once intent has been expressed or it's
     * moved further.
     */
    public function destroy(Request $request, Order $order): RedirectResponse
    {
        $creatorId = $order->type === 'offer' ? $order->seller_id : $order->buyer_id;

        if ($creatorId !== $request->user()->id) {
            throw new AccessDeniedHttpException('Only the order creator can delete it.');
        }

        if ($order->status !== 'open') {
            throw ValidationException::withMessages(['status' => 'Only an open order can be deleted.']);
        }

        $this->orders->destroy($order);

        return back()->with('success', 'Order deleted successfully.');
    }
}
