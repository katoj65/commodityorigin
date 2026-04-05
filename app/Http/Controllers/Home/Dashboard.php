<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\RoleMetadata;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class Dashboard extends Controller
{
    /**
     * Display the authenticated user's dashboard.
     */
    public function userDashboard(Request $request): Response
    {
        $user = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);
        $hasProfile = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile
            && $user->role === 'user';

        return Inertia::render('Dashboard', [
            'title' => 'Dashboard',
            'hasProfile' => $hasProfile,
            'currentRole' => $user->role,
            'roles' => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }
}
