<?php

namespace App\Http\Controllers\Profile;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessMemberResource;
use App\Http\Resources\BusinessProfileResource;
use App\Services\BusinessMemberService;
use App\Services\BusinessProfileService;
use App\Services\CurrencyService;
use App\Services\ProfileService;
use App\Services\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profiles,
        private readonly BusinessProfileService $businessProfiles,
        private readonly BusinessMemberService $businessMembers,
        private readonly CurrencyService $currencies,
        private readonly UserSessionService $sessions,
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
     * Store a newly created resource in storage — the user's personal
     * extended profile. The account type chosen on the onboarding form
     * (personal or business) is recorded on the user's role; a business
     * account fills in its full business details later from the Business
     * Profile page, rather than during this onboarding step.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profile_type' => ['required', 'in:personal,business'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'in:male,female,prefer_not_to_say'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ImageUploadHelper::rules(),
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
     * Display the authenticated user's profile page. Which Vue component
     * renders is decided here, by role, rather than in the frontend — a
     * business account sees its business profile, everyone else sees the
     * personal one.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $sessions = $this->sessions->forUser($request);

        if ($user->role === 'business') {
            $businessProfile = $this->businessProfiles->forUser($user->id);

            return Inertia::render('Profile/BusinessProfile', [
                'sessions' => $sessions,
                'businessProfile' => $businessProfile ? BusinessProfileResource::make($businessProfile)->resolve() : null,
                'businessTypeOptions' => $this->businessProfiles->businessTypeOptions(),
                'businessMembers' => $businessProfile
                    ? BusinessMemberResource::collection($this->businessMembers->forBusiness($businessProfile->id))->resolve()
                    : [],
            ]);
        }

        return Inertia::render('Profile/PersonalProfile', [
            'sessions' => $sessions,
        ]);
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
            'photo' => ImageUploadHelper::rules(),
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
     * Update the authenticated user's business profile details.
     */
    public function updateBusiness(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', Rule::in($this->businessProfiles->businessTypeOptions())],
            'industry' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'employee_count' => ['nullable', 'integer', 'min:0'],
            'year_established' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'description' => ['nullable', 'string', 'max:2000'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'logo' => ImageUploadHelper::rules(),
        ]);

        $logoPath = ImageUploadHelper::store($request->file('logo'), 'business-logos');
        unset($validated['logo']);

        if ($logoPath) {
            $validated['logo'] = $logoPath;
        }

        $this->businessProfiles->save($request->user(), $validated);

        return back()->with('success', 'Business profile updated successfully.');
    }

    /**
     * Delete the authenticated user's business profile (and, by cascade,
     * its registered members). The user keeps their account and role —
     * they land back on an empty business profile page afterward.
     */
    public function destroyBusiness(Request $request): RedirectResponse
    {
        $businessProfile = $this->businessProfiles->forUser($request->user()->id);

        abort_unless($businessProfile, 404);

        $businessProfile->delete();

        return redirect()->route('profile.show')->with('success', 'Business profile deleted successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
