<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Dashboard extends Controller
{
    //


public function userDashboard()
{
    return inertia::render('Dashboard',[
    'title'=>'Dashboard',

    ]);
}












}
