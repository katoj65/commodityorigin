<?php

namespace App\Http\Controllers\Escrow;

use App\Http\Controllers\Controller;
use App\Http\Resources\EscrowAccountResource;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EscrowController extends Controller
{
    public function __construct(
        private readonly EscrowService $escrow,
    ) {
    }

    /**
     * Display the escrow ledger. Admins see every escrow account on the
     * platform; buyers and sellers only see accounts tied to orders
     * they're part of.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Escrow/EscrowPage', [
            'escrows' => EscrowAccountResource::collection($this->escrow->forUser($user->id, $user->isAdmin()))->resolve(),
            'authUserId' => $user->id,
            'isAdmin' => $user->isAdmin(),
        ]);
    }
}
