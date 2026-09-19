<?php

namespace App\Http\Controllers\About;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    /**
     * Display the public "About" page.
     */
    public function index(): Response
    {
        return Inertia::render('About/Index');
    }
}
