<?php

namespace App\Http\Controllers\Profile;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profiles,
        private readonly CurrencyService $currencies,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profile_type' => ['required', 'in:personal,business'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => [Rule::requiredIf($request->input('profile_type') === 'personal'), 'nullable', 'in:male,female,prefer_not_to_say'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $profileType = $validated['profile_type'];
        unset($validated['profile_type']);

        $photoPath = ImageUploadHelper::store($request->file('photo'), 'profile-photos');
        unset($validated['photo']);

        if ($photoPath) {
            $validated['profile_photo'] = $photoPath;
        }

        $this->profiles->save($request->user(), $validated);
        $this->profiles->setAccountType($request->user(), $profileType);

        return redirect()->route('dashboard')->with('success', 'Profile saved successfully.');
    }

    /**
     * Update the authenticated user's selected role.
     */
    public function updateRole(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => [
                'required',
                'string',
                Rule::exists('roles_metadata', 'slug')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $this->profiles->selectRole($request->user(), $request->string('role')->toString());

        return back()->with('success', 'Role selected successfully.');
    }

    /**
     * Update the authenticated user's preferred settlement currency.
     */
    public function updateCurrency(Request $request): RedirectResponse
    {
        $request->validate([
            'currency_code' => [
                'required',
                'string',
                Rule::exists('currencies', 'code')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $this->currencies->setUserCurrency($request->user(), $request->string('currency_code')->toString());

        return back()->with('success', 'Currency updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the authenticated user's extended profile details. Unlike
     * store() (used during onboarding), this does not touch profile_type /
     * users.role — editing your bio or address shouldn't change your
     * account role.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', 'in:male,female,prefer_not_to_say'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $request->user()->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
        ]);
        unset($validated['first_name'], $validated['last_name']);

        $photoPath = ImageUploadHelper::store($request->file('photo'), 'profile-photos');
        unset($validated['photo']);

        if ($photoPath) {
            $validated['profile_photo'] = $photoPath;
        }

        $this->profiles->save($request->user(), $validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
