<?php

namespace App\Http\Controllers\HowItWorks;

use App\Http\Controllers\Controller;
use App\Http\Resources\HowItWorksStepResource;
use App\Services\HowItWorksService;
use Inertia\Inertia;
use Inertia\Response;

class HowItWorksController extends Controller
{
    public function __construct(private readonly HowItWorksService $howItWorks)
    {
    }

    /**
     * Display the public "How It Works" page.
     */
    public function index(): Response
    {
        return Inertia::render('HowItWorks/Index', [
            'steps' => HowItWorksStepResource::collection($this->howItWorks->activeSteps())->resolve(),
        ]);
    }
}
