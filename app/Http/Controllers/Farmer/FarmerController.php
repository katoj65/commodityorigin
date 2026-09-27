<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmerResource;
use App\Http\Resources\FarmResource;
use App\Models\Batch;
use App\Models\BatchFarmCollection;
use App\Models\Cooperative;
use App\Models\EscrowAccount;
use App\Models\Farm;
use App\Models\FarmCollection;
use App\Models\Farmer;
use App\Models\Lot;
use App\Models\LotBatch;
use App\Models\Market;
use App\Models\RoleMetadata;
use App\Services\FarmerService;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FarmerController extends Controller
{
    public function __construct(private readonly FarmerService $farmers)
    {
    }

    /**
     * Display the farmer's home dashboard at /farmer — the farmer
     * directory listing this used to render moved out; every
     * authenticated user lands on their own dashboard here, matching
     * the pattern the other role dashboards use (see
     * Dashboard::farmerDashboard(), which this mirrors).
     */
    public function index(Request $request): Response
    {
        $user = $request->user()->loadMissing('profile');
        $roles = RoleMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name', 'description']);

        $hasProfile = ! is_null($user->profile);
        $showSelectRoleModal = $hasProfile && $user->role === 'user';

        // My Registered Farms — real Farm rows owned by this user
        // (user_id, same scoping FarmService::listForUser() uses for the
        // "My Farms" list page).
        $farms = Farm::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(5)
            ->withQueryString();

        // KPI strip — derived from ALL of this user's farms (not just the
        // current page of the table above) plus the farm collections
        // recorded against them. FarmCollection.status is the
        // batched/unbatched lifecycle flag set in BatchService::attach()
        // ('pending' = still available stock, 'batched' = already
        // consumed into a batch), which is what "available" means here.
        $allFarms = Farm::query()->where('user_id', $user->id)->get(['id', 'status', 'total_area', 'coffee_area', 'latitude', 'longitude']);
        $farmCount = $allFarms->count();
        $pendingFarmCount = $allFarms->where('status', 'pending')->count();
        $cultivatedAreaHa = $allFarms->sum(fn (Farm $farm) => $farm->coffee_area ?? $farm->total_area ?? 0);

        $collections = FarmCollection::query()
            ->whereIn('farm_id', $allFarms->pluck('id'))
            ->get(['id', 'status', 'quantity', 'collection_price']);
        $availableCollections = $collections->where('status', 'pending');
        $availableQuantityKg = (float) $availableCollections->sum('quantity');
        $portfolioValue = (float) $availableCollections->sum(fn (FarmCollection $c) => (float) ($c->collection_price ?? 0) * (float) $c->quantity);
        $avgPricePerKg = $availableQuantityKg > 0 ? $portfolioValue / $availableQuantityKg : null;

        $pipeline = $this->traceabilityPipeline($user->id, $allFarms, $collections);

        return Inertia::render('Dashboards/DashboardFarmer', [
            'title' => 'Farmer Dashboard',
            'hasProfile' => $hasProfile,
            'currentRole' => $user->role,
            'roles' => $roles,
            'showSelectRoleModal' => $showSelectRoleModal,
            'myFarms' => [
                'data' => FarmResource::collection($farms->items())->resolve(),
                'meta' => [
                    'current_page' => $farms->currentPage(),
                    'last_page' => $farms->lastPage(),
                    'per_page' => $farms->perPage(),
                    'total' => $farms->total(),
                    'from' => $farms->firstItem(),
                    'to' => $farms->lastItem(),
                ],
            ],
            'farmKpis' => [
                'farm_count' => $farmCount,
                'pending_farm_count' => $pendingFarmCount,
                'cultivated_area_ha' => round($cultivatedAreaHa, 2),
                'available_collection_count' => $availableCollections->count(),
                'available_quantity_kg' => round($availableQuantityKg, 2),
                'portfolio_value' => round($portfolioValue, 2),
                'avg_price_per_kg' => $avgPricePerKg !== null ? round($avgPricePerKg, 2) : null,
            ],
            'pipeline' => $pipeline,
        ]);
    }

    /**
     * Build the "Physical Coffee Traceability Flow" pipeline strip —
     * real counts/volumes traced through the same collection → batch →
     * lot → market → escrow chain FarmController::pipelineSummary()
     * follows for a single farm, aggregated here across every farm this
     * user owns. There is no Harvest step: the harvest tables were
     * permanently dropped (see 2026_08_27_100000_drop_harvest_tables.php)
     * and no replacement exists, so the chain goes straight from Farm to
     * Collection.
     *
     * @param  \Illuminate\Support\Collection<int, Farm>  $farms
     * @param  \Illuminate\Support\Collection<int, FarmCollection>  $collections
     * @return array<int, array<string, mixed>>
     */
    private function traceabilityPipeline(int $userId, Collection $farms, Collection $collections): array
    {
        $gpsMappedFarmCount = $farms->filter(fn (Farm $f) => $f->latitude !== null && $f->longitude !== null)->count();
        $activeFarmCount = $farms->where('status', 'active')->count();
        $cultivatedAreaHa = round($farms->sum(fn (Farm $f) => $f->coffee_area ?? $f->total_area ?? 0), 2);

        $collectionIds = $collections->pluck('id');
        $batchedCollectionCount = BatchFarmCollection::query()
            ->whereIn('farm_collection_id', $collectionIds)
            ->pluck('farm_collection_id')
            ->unique()
            ->count();
        $totalCollectionKg = (float) $collections->sum('quantity');

        $batchIds = BatchFarmCollection::query()
            ->whereIn('farm_collection_id', $collectionIds)
            ->pluck('batch_id')
            ->unique();
        $batches = Batch::query()->whereIn('id', $batchIds)->latest()->get(['id', 'batch_number', 'weight', 'processing_method']);
        $latestBatch = $batches->first();

        $lotLinks = LotBatch::query()->whereIn('batch_id', $batchIds)->get(['lot_id']);
        $lotIds = $lotLinks->pluck('lot_id')->unique();
        $lots = Lot::query()->whereIn('id', $lotIds)->get(['id', 'grade', 'process', 'net_weight_kg']);
        $tokenisedLotCount = Lot::query()
            ->join('blockchains', 'blockchains.lot_id', '=', 'lots.id')
            ->whereIn('lots.id', $lotIds)
            ->count();

        $liveMarkets = Market::query()->whereIn('lot_id', $lotIds)->where('status', 'live')->get(['id', 'price_per_unit', 'currency']);
        $avgListedPrice = $liveMarkets->count() > 0 ? $liveMarkets->avg('price_per_unit') : null;

        $escrowHolds = EscrowAccount::query()->where('seller_id', $userId)->where('status', 'held')->get(['id', 'amount']);

        return [
            'nodes' => [
                [
                    'step' => '1. Farm',
                    'value' => "{$activeFarmCount} Active",
                    'sub' => "{$cultivatedAreaHa} ha verified",
                    'tag' => "GPS Polygons ({$gpsMappedFarmCount})",
                ],
                [
                    'step' => '2. Collection',
                    'value' => $totalCollectionKg,
                    'value_unit' => 'kg',
                    'sub' => 'Gate receipts',
                    'tag' => "{$batchedCollectionCount} Batched",
                    'tone' => 'secondary',
                ],
                [
                    'step' => '3. Batch Mill',
                    'value' => (float) $batches->sum('weight'),
                    'value_unit' => 'kg',
                    'sub' => $latestBatch?->processing_method ?? 'Awaiting milling',
                    'tag' => $latestBatch?->batch_number ?? 'No Batches Yet',
                ],
                [
                    'step' => '4. Export Lot',
                    'value' => (float) $lots->sum('net_weight_kg'),
                    'value_unit' => 'kg',
                    'sub' => $lots->first()?->process ?? 'Not yet lotted',
                    'tag' => "{$tokenisedLotCount} Tokenised",
                    'tone' => 'tertiary',
                ],
                [
                    'step' => '5. Exchange',
                    'value' => $liveMarkets->count() > 0 ? 'Listed' : 'Not Listed',
                    'sub' => $avgListedPrice !== null ? '$' . number_format((float) $avgListedPrice, 2) . ' / kg' : 'No active listings',
                    'tag' => $liveMarkets->count() > 0 ? "{$liveMarkets->count()} Live in Marketplace" : 'No Listings',
                ],
            ],
            'escrow' => [
                'value' => (float) $escrowHolds->sum('amount'),
                'sub' => 'Smart Escrow Hold',
                'tag' => $escrowHolds->count() > 0 ? "{$escrowHolds->count()} Active Holds" : 'No Active Holds',
            ],
        ];
    }

    /**
     * Show the farmer registration form.
     */
    public function create(): Response
    {
        Gate::authorize('create', Farmer::class);

        return Inertia::render('Farmer/Create', [
            'cooperatives' => Cooperative::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Handle a farmer registration submission.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Farmer::class);

        $validated = $this->validated($request);

        $farmer = $this->farmers->create([
            ...$validated,
            'user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('farmer.show', $farmer)
            ->with('success', 'Farmer registered successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farmer $farmer): Response
    {
        Gate::authorize('view', $farmer);

        $farmer->load('cooperative');

        return Inertia::render('Farmer/FarmerProfile', [
            'farmer' => FarmerResource::make($farmer)->resolve(),
            'cooperatives' => Cooperative::query()->orderBy('name')->get(['id', 'name']),
            'canCreateFarm' => Gate::allows('create', Farm::class),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Farmer $farmer): RedirectResponse
    {
        Gate::authorize('update', $farmer);

        $validated = $this->validated($request, $farmer);

        $this->farmers->update($farmer, $validated);

        return redirect()
            ->route('farmer.show', $farmer)
            ->with('success', 'Farmer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Farmer $farmer): RedirectResponse
    {
        Gate::authorize('delete', $farmer);

        $this->farmers->delete($farmer);

        return redirect()
            ->route('farmer.index')
            ->with('success', 'Farmer removed successfully.');
    }

    /**
     * Shared validation rules for store/update. `farmer_number` and
     * `national_id` must be unique, ignoring the current record on update.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Farmer $farmer = null): array
    {
        return $request->validate([
            'farmer_number' => ['nullable', 'string', 'max:50', 'unique:farmers,farmer_number,'.($farmer?->id ?? 'NULL')],
            'cooperative_id' => ['nullable', 'integer', 'exists:cooperatives,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'tel' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'subcounty' => ['nullable', 'string', 'max:255'],
            'parish' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50', 'unique:farmers,national_id,'.($farmer?->id ?? 'NULL')],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'verification_status' => ['nullable', 'string', 'in:pending,verified,rejected'],
        ]);
    }
}
