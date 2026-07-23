<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Builder;

class ProfileService
{
    /**
     * Get a base query builder for profiles.
     */
    public function query(): Builder
    {
        return UserProfile::query();
    }

    /**
     * Get a user's extended profile, if they have one.
     */
    public function forUser(int $userId): ?UserProfile
    {
        return UserProfile::where('user_id', $userId)->first();
    }

    /**
     * Create a new extended profile for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): UserProfile
    {
        return UserProfile::create([...$data, 'user_id' => $user->id]);
    }

    /**
     * Update an existing extended profile.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(UserProfile $profile, array $data): UserProfile
    {
        $profile->update($data);

        return $profile;
    }

    /**
     * Delete a user's extended profile.
     */
    public function destroy(UserProfile $profile): void
    {
        $profile->delete();
    }

    /**
     * Create or update a user's extended profile in one call — used by the
     * profile form, which doesn't need to know whether one already exists.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(User $user, array $data): UserProfile
    {
        return UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            $data,
        );
    }

    /**
     * Select a user's active role — records it on user_roles and sets it
     * as the user's current role. Requires a profile to already exist.
     */
    public function selectRole(User $user, string $role): UserRole
    {
        abort_unless($user->profile()->exists(), 403, 'Create a profile before selecting a role.');

        $userRole = UserRole::firstOrCreate([
            'user_id' => $user->id,
            'role' => $role,
        ]);

        $user->forceFill(['role' => $role])->save();

        return $userRole;
    }
}
