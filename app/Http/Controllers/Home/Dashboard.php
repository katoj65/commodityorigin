<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
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

        return Inertia::render('Dashboard', [
            'title' => 'Dashboard',
            'hasProfile' => ! is_null($user->profile),
        ]);
    }
}
