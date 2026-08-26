<?php

namespace App\Http\Controllers\FarmCollection;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmCollectionResource;
use App\Models\CropVarietyMetadata;
use App\Models\Currency;
use App\Models\FarmCollection;
use App\Models\SeasonMetadata;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FarmCollectionController extends Controller
{
    /**
     * Display a single farm collection's details.
     */
    public function show(FarmCollection $collection): Response
    {
        Gate::authorize('view', $collection);

        $collection->load(['farm', 'user']);

        return Inertia::render('FarmCollection/FarmCollectionProfile', [
            'collection' => FarmCollectionResource::make($collection)->resolve(),
            'coffeeTypeOptions' => CropVarietyMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'harvestSeasonOptions' => SeasonMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'currencyOptions' => Currency::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->pluck('code'),
        ]);
    }
}
