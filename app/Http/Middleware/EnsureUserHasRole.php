<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Ensure the authenticated user has one of the allowed roles.
     *
     * @param  array<int, string>  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        // Administrators can access all role-protected routes.
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if (empty($roles) || ! $user->hasAnyRole($roles)) {
            abort(403, 'You are not authorized to access this resource.');
        }

        return $next($request);
    }
}
