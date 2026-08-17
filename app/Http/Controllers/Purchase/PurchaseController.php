<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserOrderResource;
use App\Models\UserOrder;
use App\Services\UserOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PurchaseController extends Controller
{
    public function __construct(private readonly UserOrderService $purchases)
    {
    }

    /**
     * Display the authenticated user's purchase history — a simple,
     * read-only receipt per checkout, separate from the marketplace's
     * request/offer Order workflow.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Purchase/PurchasePage', [
            'purchases' => UserOrderResource::collection($this->purchases->forUser($request->user()->id))->resolve(),
        ]);
    }

    /**
     * Display a single purchase receipt in full detail.
     */
    public function show(Request $request, UserOrder $userOrder): Response
    {
        $this->authorizeOwner($request, $userOrder);

        return Inertia::render('Purchase/PurchaseProfile', [
            'purchase' => UserOrderResource::make($userOrder)->resolve(),
        ]);
    }

    /**
     * Cancel a purchase receipt. Owner only.
     */
    public function cancel(Request $request, UserOrder $userOrder): RedirectResponse
    {
        $this->authorizeOwner($request, $userOrder);

        $this->purchases->cancel($userOrder);

        return back()->with('success', 'Order cancelled successfully.');
    }

    /**
     * Ensure the authenticated user owns the given purchase receipt.
     */
    private function authorizeOwner(Request $request, UserOrder $userOrder): void
    {
        if ($userOrder->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this order.');
        }
    }
}
