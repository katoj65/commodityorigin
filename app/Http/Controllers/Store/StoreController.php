<?php

namespace App\Http\Controllers\Store;

use App\Helpers\ExcelImportHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BatchResource;
use App\Http\Resources\FarmCollectionResource;
use App\Http\Resources\LotResource;
use App\Http\Resources\StoreItemResource;
use App\Http\Resources\StoreResource;
use App\Models\Batch;
use App\Models\FarmCollection;
use App\Models\Lot;
use App\Models\CropVarietyMetadata;
use App\Models\Currency;
use App\Models\DryingMethodMetadata;
use App\Models\FlavorMetadata;
use App\Models\MillingMetadata;
use App\Models\ProcessingMetadata;
use App\Models\SeasonMetadata;
use App\Models\Store;
use App\Models\StoreItem;
use App\Services\CoffeeGradeService;
use App\Services\CountryService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoreController extends Controller
{
    public function __construct(
        private readonly StoreService $stores,
        private readonly CoffeeGradeService $coffeeGrades,
        private readonly CountryService $countries,
    ) {
    }

    /**
     * Display the authenticated user's store — a registration prompt if
     * they don't have one, or a pending/rejected notice while awaiting
     * admin verification. Once verified, there's nothing left for this
     * gate page to show, so it hands off straight to the inventory's
     * default tab (Farm Collections). Admins also see every store
     * awaiting review here, regardless of their own store's status.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $store = $this->stores->forUser($request->user()->id);

        if ($store?->isVerified()) {
            return redirect()->route('store.collections');
        }

        return Inertia::render('Store/StorePage', [
            ...$this->headerContext($request, $store),
            'isAdmin' => $request->user()->isAdmin(),
            'pendingStores' => $request->user()->isAdmin()
                ? StoreResource::collection($this->stores->pending())->resolve()
                : [],
        ]);
    }

    /**
     * The four inventory tabs, now split into their own full pages —
     * Farm Collections, Batches, Lots, and Tokenised Lots — all sharing
     * the same StoreInventoryLayout shell (hero, KPI snapshot, tab nav,
     * quick-transfer rail) and therefore the same full dataset, since the
     * KPI snapshot on every tab summarizes all four collections at once.
     */
    public function collections(Request $request): Response|RedirectResponse
    {
        return $this->renderInventoryTab($request, 'Store/FarmCollections');
    }

    public function batches(Request $request): Response|RedirectResponse
    {
        return $this->renderInventoryTab($request, 'Store/Batches');
    }

    public function lots(Request $request): Response|RedirectResponse
    {
        return $this->renderInventoryTab($request, 'Store/Lots');
    }

    public function tokenised(Request $request): Response|RedirectResponse
    {
        return $this->renderInventoryTab($request, 'Store/TokenisedLots');
    }

    private function renderInventoryTab(Request $request, string $component): Response|RedirectResponse
    {
        $store = $this->stores->forUser($request->user()->id);

        if (! $store?->isVerified()) {
            return redirect()->route('store.show');
        }

        return Inertia::render($component, [
            ...$this->headerContext($request, $store),
            ...$this->inventoryContext($request->user()->id),
        ]);
    }

    /**
     * The data every inventory tab page needs — the three collections
     * themselves (each tab lists one, but the shared KPI snapshot totals
     * all three) plus every option list the "Register New ▾" modals need,
     * since that dropdown lives in the shared layout, not any one tab.
     *
     * @return array<string, mixed>
     */
    private function inventoryContext(int $userId): array
    {
        return [
            'farmCollections' => FarmCollectionResource::collection(
                FarmCollection::query()
                    ->where('user_id', $userId)
                    ->with('farm')
                    ->latest('collection_date')
                    ->get()
            )->resolve(),
            'batches' => BatchResource::collection(
                Batch::query()->where('user_id', $userId)->latest()->get()
            )->resolve(),
            'lots' => LotResource::collection(
                Lot::query()->where('user_id', $userId)->with(['lotBatches.batch', 'blockchain'])->latest()->get()
            )->resolve(),
            'processOptions' => ProcessingMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'dryingMethodOptions' => DryingMethodMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
            'millingOptions' => MillingMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name'),
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
            'coffeeGradeOptions' => $this->coffeeGrades->activeOptions()->pluck('name')->all(),
            'packagingTypeOptions' => ['GrainPro', 'Jute Only', 'Vacuum'],
            'originOptions' => $this->countries->coffeeProducers()->pluck('name')->all(),
            'currencyOptions' => Currency::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->pluck('code'),
            // The currency's own real-world country/union, for a "USD —
            // United States" style label on the New Lot form's currency
            // dropdown.
            'currencyCountries' => Currency::query()->pluck('country', 'code'),
            'flavorOptions' => FlavorMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'slug', 'name'])
                ->map(fn (FlavorMetadata $flavor): array => [
                    'id' => $flavor->id,
                    'slug' => $flavor->slug,
                    'name' => $flavor->name,
                ]),
        ];
    }

    /**
     * Display the store market — a browsable preview of items across
     * stores. Sits alongside the inventory page under the same store
     * layout, so it shares the same header context (store, statuses,
     * import result) that drives the header's action buttons.
     */
    public function market(Request $request): Response
    {
        $store = $this->stores->forUser($request->user()->id);

        return Inertia::render('Store/Market', [
            ...$this->headerContext($request, $store),
            'items' => StoreItemResource::collection($this->stores->browsable())->resolve(),
        ]);
    }

    /**
     * Props every store-layout page needs so the header's action buttons
     * (request/resubmit, add item, import, status filter) behave
     * identically regardless of which store section is being viewed.
     *
     * @return array<string, mixed>
     */
    private function headerContext(Request $request, ?Store $store): array
    {
        return [
            'store' => $store ? StoreResource::make($store->load('verifiedBy'))->resolve() : null,
            'statusOptions' => StoreItem::STATUSES,
            'importResult' => session('import_result'),
        ];
    }

    /**
     * Request the authenticated user's store, confirmed with their own
     * email and password. Works for a first-time request or to resubmit
     * a previously rejected store.
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->stores->confirmationRules());

        $this->stores->requestStore($request->user(), $validated['email'], $validated['password']);

        return back()->with('success', 'Store requested. An admin will review it before you can open it.');
    }

    /**
     * Verify a pending store — admin only.
     */
    public function verify(Request $request, Store $store): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $this->stores->verify($store, $request->user());

        return back()->with('success', "{$store->user->name}'s store has been verified.");
    }

    /**
     * Reject a pending store — admin only.
     */
    public function reject(Request $request, Store $store): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->stores->reject($store, $request->user(), $validated['reason'] ?? null);

        return back()->with('success', "{$store->user->name}'s store has been rejected.");
    }

    /**
     * Add a new item to the authenticated user's store. The store must
     * already be verified by an admin.
     */
    public function storeItem(Request $request): RedirectResponse
    {
        $store = $this->ownStore($request);

        if (! $store->isVerified()) {
            return back()->with('error', 'Your store must be verified by an admin before you can add items.');
        }

        $validated = $request->validate($this->stores->itemRules());

        $item = $this->stores->addItem($store, $validated, $request->user());

        return redirect()->route('store.items.show', $item)->with('success', 'Item added to your store.');
    }

    /**
     * Display a single item's own page — its details plus full status
     * history.
     */
    public function showItem(Request $request, StoreItem $storeItem): Response
    {
        $this->authorizeItem($storeItem, $request);

        $storeItem->load('statusLogs.changedBy:id,first_name,last_name');

        return Inertia::render('Store/StoreItemPage', [
            ...$this->headerContext($request, $storeItem->store),
            'item' => StoreItemResource::make($storeItem)->resolve(),
        ]);
    }

    /**
     * Bulk-add items from an uploaded spreadsheet. The store must already
     * be verified by an admin.
     */
    public function importItems(Request $request): RedirectResponse
    {
        $store = $this->ownStore($request);

        if (! $store->isVerified()) {
            return back()->with('error', 'Your store must be verified by an admin before you can add items.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $rows = ExcelImportHelper::readRows($request->file('file'));

        if ($rows === []) {
            return back()->with('error', 'The file has no data rows to import.');
        }

        $result = $this->stores->importItems($store, $rows, $request->user());

        session()->flash('import_result', $result);

        if ($result['imported'] === 0) {
            return back()->with('error', 'No items were imported — every row had errors.');
        }

        $message = $result['imported'].' item'.($result['imported'] === 1 ? '' : 's').' imported successfully.';
        if ($result['errors'] !== []) {
            $message .= ' '.count($result['errors']).' row(s) were skipped due to errors.';
        }

        return back()->with('success', $message);
    }

    /**
     * Update an item's own details.
     */
    public function updateItem(Request $request, StoreItem $storeItem): RedirectResponse
    {
        $this->authorizeItem($storeItem, $request);

        $validated = $request->validate($this->stores->itemRules());

        $this->stores->updateItem($storeItem, $validated);

        return back()->with('success', 'Item updated successfully.');
    }

    /**
     * Move an item to a new status — logged automatically for
     * traceability.
     */
    public function updateItemStatus(Request $request, StoreItem $storeItem): RedirectResponse
    {
        $this->authorizeItem($storeItem, $request);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', StoreItem::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->stores->changeStatus($storeItem, $validated['status'], $validated['notes'] ?? null, $request->user());

        return back()->with('success', 'Item status updated.');
    }

    /**
     * Remove an item from the store.
     */
    public function destroyItem(Request $request, StoreItem $storeItem): RedirectResponse
    {
        $this->authorizeItem($storeItem, $request);

        $this->stores->destroyItem($storeItem);

        return redirect()->route('store.show')->with('success', 'Item removed from store.');
    }

    /**
     * The authenticated user's own store, or a 404 if they don't have one.
     */
    private function ownStore(Request $request): Store
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('You do not have a store yet.');
        }

        return $store;
    }

    /**
     * Guard item actions to whoever owns the store the item belongs to.
     */
    private function authorizeItem(StoreItem $item, Request $request): void
    {
        if ($item->store->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not manage this item.');
        }
    }

    /**
     * Guard store verification actions to admins only.
     */
    private function authorizeAdmin(Request $request): void
    {
        if (! $request->user()->isAdmin()) {
            throw new AccessDeniedHttpException('Only admins may verify stores.');
        }
    }
}
