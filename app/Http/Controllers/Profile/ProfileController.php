<?php

namespace App\Http\Controllers\Profile;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profiles,
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
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,prefer_not_to_say'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $photoPath = ImageUploadHelper::store($request->file('photo'), 'profile-photos');
        unset($validated['photo']);

        if ($photoPath) {
            $validated['profile_photo'] = $photoPath;
        }

        $this->profiles->save($request->user(), $validated);

        return back()->with('success', 'Profile saved successfully.');
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
