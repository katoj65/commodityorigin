<?php

namespace App\Http\Controllers\Currency;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CurrencyController extends Controller
{
    public function __construct(private readonly CurrencyService $currencies)
    {
    }

    /**
     * Display the currency management page. Every authenticated user may
     * view it; only admins can create, edit, or delete currencies.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Currency/CurrencyPage', [
            'currencies' => CurrencyResource::collection($this->currencies->all())->resolve(),
            'canManage' => Gate::allows('create', Currency::class),
        ]);
    }

    /**
     * Create a new currency. Admins only.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Currency::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:3', 'uppercase', 'unique:currencies,code'],
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:8'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $this->currencies->create($validated);

        return back()->with('success', 'Currency created successfully.');
    }

    /**
     * Update an existing currency. Admins only.
     */
    public function update(Request $request, Currency $currency): RedirectResponse
    {
        Gate::authorize('update', $currency);

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:3', 'uppercase', Rule::unique('currencies', 'code')->ignore($currency->id)],
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:8'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $this->currencies->update($currency, $validated);

        return back()->with('success', 'Currency updated successfully.');
    }

    /**
     * Delete a currency. Admins only.
     */
    public function destroy(Currency $currency): RedirectResponse
    {
        Gate::authorize('delete', $currency);

        $this->currencies->delete($currency);

        return back()->with('success', 'Currency deleted successfully.');
    }
}
