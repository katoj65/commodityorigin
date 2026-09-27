<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Backs the "Continue with Google" buttons on both the Login and
 * Register pages (Auth/Login.vue, Auth/Register.vue) — one redirect/
 * callback pair for both, since Google's OAuth flow doesn't distinguish
 * signing in from signing up (see GoogleAuthService::findOrCreateUser()).
 */
class GoogleAuthController extends Controller
{
    public function __construct(private readonly GoogleAuthService $googleAuth)
    {
    }

    /**
     * Send the user to Google's consent screen.
     */
    public function redirect(): RedirectResponse|Response
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google's redirect back, resolve the verified account to a
     * local user (logging in or registering as GoogleAuthService
     * decides), and start their session.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            // InvalidStateException carries no message (Socialite throws
            // it bare) — log the exception class too, otherwise a state
            // mismatch and every other failure mode look identical here.
            Log::warning('Google OAuth callback failed.', [
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')->with('status', 'Google sign-in failed. Please try again.');
        }

        $user = $this->googleAuth->findOrCreateUser($googleUser);

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
