<?php

namespace App\Http\Middleware;

use App\Services\ProfileService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    public function __construct(private readonly ProfileService $profiles)
    {
    }

    /**
     * Block navigation to any other authenticated page until the user has
     * filled in their profile. The dashboard itself stays reachable — it's
     * where ProfileTypeModal.vue renders and blocks interaction until the
     * profile is saved — as does the endpoint that submits it and logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $request->routeIs('dashboard', 'profile.store', 'logout')) {
            return $next($request);
        }

        if (is_null($this->profiles->forUser($user->id))) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
