<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Http\Resources\ExchangeRateResource;
use App\Http\Resources\TaskResource;
use App\Models\CropGradeMetadata;
use App\Models\RoleMetadata;
use App\Services\CalendarService;
use App\Services\ExchangeRateService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\LotRequest;

class Dashboard extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates,
        private readonly CalendarService $calendar,
        private readonly TaskService $tasks,
    ) {
    }

    /**
     * Legacy entry point — role-based routing was moved to dashboard().
     * Kept to avoid breaking any existing route references.
     */
    public function userDashboard()
    {
        // Role-specific routing is commented out; all roles now route through dashboard()
        // if ($role === 'buyer') {
        //     return self::buyerDashboard($request);
        // } elseif ($role === 'seller') {
        //     return self::sellerDashboard($request);
        // } elseif ($role === 'admin') {
        //     return self::adminDashboard($request);
        // } elseif ($role === 'investor') {
        //     return self::investorDashboard($request);
        // } elseif ($role === 'farmer') {
        //     return self::farmerDashboard($request);
        // } elseif ($role === 'exporter') {
        //     return self::exporterDashboard($request);
        // }

        return Inertia::render('Dashboard');
    }


    // ── Role-specific dashboards ─────────────────────────────────────────────
    // Each method loads the user profile, active roles, and determines whether
    // the role-selection modal should be shown on first load.

    /** Buyer-facing dashboard */
    static function buyerDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboards/BuyerDashboard', [
            'title'               => 'Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Seller-facing dashboard */
    static function sellerDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboard', [
            'title'               => 'Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Admin-facing dashboard */
    static function adminDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboards/AdminDashboard', [
            'title'               => 'Admin Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Investor-facing dashboard */
    static function investorDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboards/DashboardInvestor', [
            'title'               => 'Investor Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Farmer-facing dashboard */
    static function farmerDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile          = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        return Inertia::render('Dashboards/DashboardFarmer', [
            'title'               => 'Farmer Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }

    /** Exporter-facing dashboard */
    static function exporterDashboard(Request $request)
    {
        $user  = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';


        return Inertia::render('Dashboards/DashboardExporter', [
            'title'               => 'Exporter Dashboard',
            'hasProfile'          => $hasProfile,
            'currentRole'         => $user->role,
            'roles'               => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
        ]);
    }


    // ── Primary authenticated dashboard ─────────────────────────────────────

    /**
     * Default dashboard for all authenticated users.
     * Passes roles (for the role-selection modal) and crop grades
     * (for the Quick Buy modal grade dropdown).
     */
    public function dashboard(Request $request)
    {
        $user       = $request->user()->loadMissing('profile', 'userRole');
        $hasProfile = ! is_null($user->profile);
        $hasRole    = ! is_null($user->userRole);

        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $cropGrades = CropGradeMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'name']);

        $lotRequest = LotRequest::query()
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();


        return Inertia::render('GeneralDashboard', [
            'hasProfile' => $hasProfile,
            'hasRole'    => $hasRole,
            'roles'      => $roles,
            'cropGrades' => $cropGrades,
            'lotRequests' => $lotRequest,
            'exchangeRates' => ExchangeRateResource::collection($this->exchangeRates->all())->resolve(),
            'calendarEvents' => CalendarResource::collection($this->calendar->eventsForUser($user->id))->resolve(),
            'tasks' => TaskResource::collection($this->tasks->tasksForUser($user->id)->take(6))->resolve(),
        ]);
    }
}
