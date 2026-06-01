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
public function userDashboard(Request $request)
{

$role = $request->user()->role;

// if ($role === 'buyer') {
// return self::buyerDashboard($request);
// } elseif ($role === 'seller') {
// return self::sellerDashboard($request);
// } elseif ($role === 'admin') {
// return self::adminDashboard($request);
// }elseif ($role === 'investor') {
// return self::investorDashboard($request);
// }elseif ($role === 'farmer') {
// return self::farmerDashboard($request);
// }elseif ($role === 'exporter') {
// return self::exporterDashboard($request);
// }


// Default fallback for 'user' role and any other role
// return Inertia::render('Dashboards', [
// 'title' => 'AdminDashboard',
// ]);

return Inertia::render('Dashboard');


}








//buyer dashboard
static function buyerDashboard($request)
{

$user = $request->user()->loadMissing('profile');
$roles = RoleMetadata::query()
->where('is_active', true)
->orderBy('sort_order')
->get(['slug', 'name', 'description']);
$hasProfile = ! is_null($user->profile);
$showSelectRoleModal = $hasProfile
&& $user->role === 'user';

return Inertia::render('Dashboards/BuyerDashboard', [
'title' => 'Dashboard',
'hasProfile' => $hasProfile,
'currentRole' => $user->role,
'roles' => $roles,
'showSelectRoleModal' => $showSelectRoleModal,
]);


}








//seller dashboard
static function sellerDashboard($request)
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

//admin dashboard
static function adminDashboard($request)
{
$user = $request->user()->loadMissing('profile');
$roles = RoleMetadata::query()
->where('is_active', true)
->orderBy('sort_order')
->get(['slug', 'name', 'description']);
$hasProfile = ! is_null($user->profile);
$showSelectRoleModal = $hasProfile
&& $user->role === 'user';

return Inertia::render('Dashboards/AdminDashboard', [
'title' => 'Admin Dashboard',
'hasProfile' => $hasProfile,
'currentRole' => $user->role,
'roles' => $roles,
'showSelectRoleModal' => $showSelectRoleModal,
]);
}





// investor dashboard
static function investorDashboard($request)
{
$user = $request->user()->loadMissing('profile');
$roles = RoleMetadata::query()
->where('is_active', true)
->orderBy('sort_order')
->get(['slug', 'name', 'description']);
$hasProfile = ! is_null($user->profile);
$showSelectRoleModal = $hasProfile
&& $user->role === 'user';

return Inertia::render('Dashboards/DashboardInvestor', [
'title' => 'Investor Dashboard',
'hasProfile' => $hasProfile,
'currentRole' => $user->role,
'roles' => $roles,
'showSelectRoleModal' => $showSelectRoleModal,
]);
}



//farmer dashboard
static function farmerDashboard($request){
$user = $request->user()->loadMissing('profile');
$roles = RoleMetadata::query()
->where('is_active', true)
->orderBy('sort_order')
->get(['slug', 'name', 'description']);
$hasProfile = ! is_null($user->profile);
$showSelectRoleModal = $hasProfile
&& $user->role === 'user';
return Inertia::render('Dashboards/DashboardFarmer', [
'title' => 'Farmer Dashboard',
'hasProfile' => $hasProfile,
'currentRole' => $user->role,
'roles' => $roles,
'showSelectRoleModal' => $showSelectRoleModal,
]);



}


static function exporterDashboard($request){
$user = $request->user()->loadMissing('profile');
$roles = RoleMetadata::query()
->where('is_active', true)
->orderBy('sort_order')
->get(['slug', 'name', 'description']);
$hasProfile = ! is_null($user->profile);
$showSelectRoleModal = $hasProfile
&& $user->role === 'user';
return Inertia::render('Dashboards/DashboardExporter', [
'title' => 'Exporter Dashboard',
'hasProfile' => $hasProfile,
'currentRole' => $user->role,
'roles' => $roles,
'showSelectRoleModal' => $showSelectRoleModal,
]);

}






// Default fallback for 'user' role and any other role
public function dashboard(Request $request)
{
    $user = $request->user()->loadMissing('profile', 'userRole');
    $hasProfile = ! is_null($user->profile);
    $hasRole    = ! is_null($user->userRole);

    $roles = RoleMetadata::query()
        ->where('is_active',1)
        ->orderBy('sort_order')
        ->get(['slug', 'name', 'description']);

    return Inertia::render('UserDashboard', [
        'hasProfile' => $hasProfile,
        'hasRole'    => $hasRole,
        'roles'      => $roles,
    ]);
}
















}
