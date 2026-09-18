<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\CropGradeMetadata;
use App\Models\CropVarietyMetadata;
use App\Models\IncotermMetadata;
use App\Models\OriginMetadata;
use App\Models\ProcessingMetadata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Welcome', [
            'filterOptions' => [
                'species' => CropVarietyMetadata::where('is_active', true)->orderBy('sort_order')->pluck('name'),
                'processing' => ProcessingMetadata::where('is_active', true)->orderBy('sort_order')->pluck('name'),
                'origins' => OriginMetadata::active()->pluck('name'),
                'grades' => CropGradeMetadata::where('is_active', true)->orderBy('sort_order')->pluck('name'),
                'incoterms' => IncotermMetadata::where('is_active', true)->orderBy('sort_order')->pluck('name'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }




















}
