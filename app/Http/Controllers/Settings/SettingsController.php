<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Http\Resources\PriceIndexResource;
use App\Models\DeliveryMethodMetadata;
use App\Models\IncotermMetadata;
use App\Models\PriceIndex;
use App\Services\ExchangeRateService;
use App\Services\PriceIndexService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates,
        private readonly PriceIndexService $priceIndexes,
    ) {
    }

    /**
     * Display the system settings page.
     */
    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'exchangeRates' => ExchangeRateResource::collection($this->exchangeRates->all())->resolve(),
            'priceIndexes' => PriceIndexResource::collection($this->priceIndexes->all())->resolve(),
            'deliveryMethods' => DeliveryMethodMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all(),
            'incoterms' => IncotermMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all(),
        ]);
    }

    /**
     * Create or update a price index entry.
     */
    public function storePriceIndex(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item' => ['required', 'string', 'max:255'],
            'current_price' => ['required', 'numeric', 'min:0'],
            'percentage_fluctuation' => ['nullable', 'numeric'],
            'status' => ['required', 'string', 'max:255'],
        ]);

        $this->priceIndexes->upsert($validated['item'], $validated);

        return back()->with('success', 'Price index added.');
    }

    /**
     * Remove a price index entry.
     */
    public function destroyPriceIndex(PriceIndex $priceIndex): RedirectResponse
    {
        $this->priceIndexes->destroy($priceIndex);

        return back()->with('success', 'Price index removed.');
    }
}
