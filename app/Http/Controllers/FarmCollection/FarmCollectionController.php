<?php

namespace App\Http\Controllers\FarmCollection;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmCollectionResource;
use App\Models\CropVarietyMetadata;
use App\Models\Currency;
use App\Models\FarmCollection;
use App\Models\SeasonMetadata;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FarmCollectionController extends Controller
{
    /**
     * Look up a farm collection by its collection_code — used by the
     * "Attach Farm Collection" modal on the Batch profile page. Open to
     * any authenticated user, same as FarmController::findByCode(); the
     * mutating action that actually links it to a batch is what's
     * policy-gated, not this lookup.
     */
    public function findByCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'collection_code' => ['required', 'string', 'max:255'],
        ]);

        $collection = FarmCollection::query()
            ->where('collection_code', $validated['collection_code'])
            ->with('farm')
            ->first();

        if (! $collection) {
            return response()->json(['message' => 'No farm collection with that code was found.'], 404);
        }

        return response()->json(FarmCollectionResource::make($collection)->resolve());
    }

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
