<?php

namespace App\Http\Controllers\Cooperative;

use App\Http\Controllers\Controller;
use App\Http\Resources\CooperativeResource;
use App\Models\Cooperative;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CooperativeController extends Controller
{
    /**
     * Redirect cooperative root traffic to the create page.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('cooperative.create');
    }

    /**
     * Show the cooperative registration form.
     */
    public function create(): Response
    {
        return Inertia::render('Cooperative/Create');
    }

    /**
     * Display the cooperative profile page.
     */
    public function show(Cooperative $cooperative): Response
    {
        return Inertia::render('Cooperative/CooperativeProfile', [
            'cooperative' => CooperativeResource::make($cooperative)->resolve(),
        ]);
    }

    /**
     * Store a newly created cooperative.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:cooperatives,code'],
            'registration_number' => ['required', 'string', 'max:100', 'unique:cooperatives,registration_number'],
            'contact_person' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'sub_county' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cooperative = Cooperative::query()->create([
            ...$validated,
            'status' => 'active',
        ]);

        return redirect()
            ->route('cooperative.show', $cooperative)
            ->with('success', 'Cooperative registered successfully.');
    }
}
