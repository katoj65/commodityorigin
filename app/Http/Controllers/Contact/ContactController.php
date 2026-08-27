<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contacts,
    ) {}

    /**
     * Display the contact page — every contact in the authenticated user's address book.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Contact/ContactsPage', [
            'contacts' => ContactResource::collection($this->contacts->contactsForUser($request->user()->id))->resolve(),
        ]);
    }

    /**
     * Create a new contact.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateContact($request);

        $this->contacts->create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return back()->with('success', 'Contact created successfully.');
    }

    /**
     * Display a single contact.
     */
    public function show(Request $request, Contact $contact): JsonResponse
    {
        $this->authorizeOwner($request, $contact);

        return response()->json([
            'contact' => ContactResource::make($contact)->resolve(),
        ]);
    }

    /**
     * Update an existing contact.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $this->authorizeOwner($request, $contact);

        $validated = $this->validateContact($request);

        $this->contacts->update($contact, $validated);

        return back()->with('success', 'Contact updated successfully.');
    }

    /**
     * Delete a contact.
     */
    public function destroy(Request $request, Contact $contact): RedirectResponse
    {
        $this->authorizeOwner($request, $contact);

        $this->contacts->destroy($contact);

        return back()->with('success', 'Contact deleted successfully.');
    }

    /**
     * Validate the create/update payload.
     *
     * @return array<string, mixed>
     */
    private function validateContact(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_favorite' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * Ensure the authenticated user owns the given contact.
     */
    private function authorizeOwner(Request $request, Contact $contact): void
    {
        if ($contact->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this contact.');
        }
    }
}
