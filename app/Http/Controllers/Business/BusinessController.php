<?php

namespace App\Http\Controllers\Business;

use App\Helpers\ExcelImportHelper;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessMemberResource;
use App\Http\Resources\BusinessProfileResource;
use App\Models\BusinessMember;
use App\Models\BusinessProfile;
use App\Services\BusinessMemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BusinessController extends Controller
{
    public function __construct(private readonly BusinessMemberService $members)
    {
    }

    /**
     * Display the business directory — every registered business profile
     * on the platform.
     */
    public function index(): Response
    {
        $businesses = BusinessProfile::query()
            ->with('user:id,first_name,last_name')
            ->withCount('members')
            ->latest()
            ->get();

        return Inertia::render('Business/BusinessesPage', [
            'businesses' => BusinessProfileResource::collection($businesses)->resolve(),
        ]);
    }

    /**
     * Display the dedicated members page for a business — the caller's
     * own business by default, or another business if an admin is
     * targeting one via `?business={id}`.
     */
    public function membersPage(Request $request): Response
    {
        $business = $this->resolveBusiness($request);

        return Inertia::render('Business/BusinessMembers', [
            'business' => BusinessProfileResource::make($business)->resolve(),
            'members' => BusinessMemberResource::collection($this->members->forBusiness($business->id))->resolve(),
            'canManage' => $this->canManage($business, $request),
            'importResult' => session('import_result'),
        ]);
    }

    /**
     * Register a new member under a business — filled in directly by the
     * business, the same way a cooperative registers its member farmers,
     * rather than by invitation.
     */
    public function storeMember(Request $request): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->authorizeManage($business, $request);

        $validated = $this->validateMember($request);

        $this->members->create($business, $validated);

        return back()->with('success', 'Member registered successfully.');
    }

    /**
     * Update a registered member's details. Only the business's owner or
     * an admin may do this.
     */
    public function updateMember(Request $request, BusinessMember $businessMember): RedirectResponse
    {
        $this->authorizeManage($businessMember->businessProfile, $request);

        $validated = $this->validateMember($request);

        $this->members->update($businessMember, $validated);

        return back()->with('success', 'Member updated successfully.');
    }

    /**
     * Remove a registered member. Only the business's owner or an admin
     * may do this.
     */
    public function destroyMember(Request $request, BusinessMember $businessMember): RedirectResponse
    {
        $this->authorizeManage($businessMember->businessProfile, $request);

        $this->members->destroy($businessMember);

        return back()->with('success', 'Member removed successfully.');
    }

    /**
     * Bulk-register members from an uploaded spreadsheet. Only the
     * business's owner or an admin may do this.
     */
    public function importMembers(Request $request): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->authorizeManage($business, $request);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $rows = ExcelImportHelper::readRows($request->file('file'));

        if ($rows === []) {
            return back()->with('error', 'The file has no data rows to import.');
        }

        $result = $this->members->importRows($business, $rows);

        session()->flash('import_result', $result);

        if ($result['imported'] === 0) {
            return back()->with('error', 'No members were imported — every row had errors.');
        }

        $message = $result['imported'].' member'.($result['imported'] === 1 ? '' : 's').' imported successfully.';
        if ($result['errors'] !== []) {
            $message .= ' '.count($result['errors']).' row(s) were skipped due to errors.';
        }

        return back()->with('success', $message);
    }

    /**
     * Validate a member registration/update payload.
     *
     * @return array<string, mixed>
     */
    private function validateMember(Request $request): array
    {
        $validated = $request->validate([
            ...$this->members->rules(),
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $photoPath = ImageUploadHelper::store($request->file('photo'), 'business-member-photos');
        unset($validated['photo']);

        if ($photoPath) {
            $validated['photo'] = $photoPath;
        }

        return $validated;
    }

    /**
     * The business a request is acting on: the authenticated user's own
     * business by default, or — for admins only — another business named
     * via a `business_profile_id` input or `business` query parameter.
     */
    private function resolveBusiness(Request $request): BusinessProfile
    {
        $businessId = $request->input('business_profile_id') ?? $request->query('business');

        if ($businessId && $request->user()->isAdmin()) {
            return BusinessProfile::query()->findOrFail($businessId);
        }

        return $this->ownBusiness($request);
    }

    /**
     * The authenticated user's own business profile, or a 404 if they
     * don't have one.
     */
    private function ownBusiness(Request $request): BusinessProfile
    {
        $business = $request->user()->businessProfile;

        if (! $business) {
            throw new NotFoundHttpException('You do not have a business profile.');
        }

        return $business;
    }

    /**
     * Whether the authenticated user may manage the given business's
     * members — its owner or an admin only.
     */
    private function canManage(BusinessProfile $business, Request $request): bool
    {
        return $business->user_id === $request->user()->id || $request->user()->isAdmin();
    }

    /**
     * Guard member-management actions to the business's owner or an admin
     * only.
     */
    private function authorizeManage(BusinessProfile $business, Request $request): void
    {
        if (! $this->canManage($business, $request)) {
            throw new AccessDeniedHttpException('You do not manage this business.');
        }
    }
}
