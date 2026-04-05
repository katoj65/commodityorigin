<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
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
        ]);

        UserProfile::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated,
        );

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

        abort_unless($request->user()->profile()->exists(), 403, 'Create a profile before selecting a role.');

        $request->user()->forceFill([
            'role' => $request->string('role')->toString(),
        ])->save();

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
