<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $authenticatedUser = $request->user()?->loadMissing('profile');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $authenticatedUser ? [
                    'id' => $authenticatedUser->id,
                    'first_name' => $authenticatedUser->first_name,
                    'last_name' => $authenticatedUser->last_name,
                    'name' => $authenticatedUser->name,
                    'email' => $authenticatedUser->email,
                    'role' => $authenticatedUser->role,
                    'telephone' => $authenticatedUser->telephone,
                    'profile_photo_path' => $authenticatedUser->profile_photo_path,
                    'profile_photo_url' => $authenticatedUser->profile_photo_url,
                    'email_verified_at' => $authenticatedUser->email_verified_at,
                    'two_factor_enabled' => ! is_null($authenticatedUser->two_factor_secret),
                    'profile' => $authenticatedUser->profile,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}
