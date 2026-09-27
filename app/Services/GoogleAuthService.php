<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    public function __construct(private readonly WalletService $wallets)
    {
    }

    /**
     * Resolve the verified Google account to a local user — logging in
     * an existing one or registering a brand-new one. Google's OAuth
     * callback doesn't tell us whether the person clicked "Continue with
     * Google" on the login page or the register page; the account that
     * does (or doesn't) already exist for this Google id/email is what
     * actually decides which path runs, so both buttons resolve here.
     */
    public function findOrCreateUser(SocialiteUser $googleUser): User
    {
        $user = User::query()->where('google_id', $googleUser->getId())->first();

        if ($user) {
            return $user;
        }

        $user = User::query()->where('email', $googleUser->getEmail())->first();

        if ($user) {
            // An existing password-based account signing in with Google
            // for the first time — link it instead of creating a
            // duplicate user for the same email.
            $user->forceFill(['google_id' => $googleUser->getId()])->save();

            return $user;
        }

        return $this->register($googleUser);
    }

    /**
     * Register a brand-new user from a verified Google account. Mirrors
     * App\Actions\Fortify\CreateNewUser: same role/verification defaults
     * and the same wallet provisioning step, since this is the Google
     * equivalent of that registration path rather than a separate one.
     */
    private function register(SocialiteUser $googleUser): User
    {
        [$firstName, $lastName] = $this->splitName($googleUser->getName(), $googleUser->getEmail());

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'user',
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            // Google-authenticated accounts never use a local password,
            // but the column is required — a random value keeps it
            // unguessable and unusable for a password-based login.
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => now(),
        ]);

        $this->wallets->ensureForUser($user->id);

        return $user;
    }

    /**
     * Split Google's single display name into first/last name, the
     * fields the rest of the app expects. Falls back to the email's
     * local part when Google returns no name at all.
     *
     * @return array{0: string, 1: string}
     */
    private function splitName(?string $name, string $email): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            return [Str::before($email, '@'), ''];
        }

        $parts = preg_split('/\s+/', $name, 2);

        return [$parts[0], $parts[1] ?? ''];
    }
}
